<?php

namespace Lyignore\LaravelOauth2\Console;

use Lyignore\LaravelOauth2\Api;
use phpseclib\Crypt\RSA;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class KeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:keys 
                                {--force : Overwrite keys they already exist}
                                {--name= : The name of the client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the encryption keys for API authentication';

    /**
     * Execute the console command.
     *
     * @param  \phpseclib\Crypt\RSA  $rsa
     * @return mixed
     */
    public function handle(RSA $rsa)
    {
        $name = $this->option('name') ?: $this->ask(
            'What should we name for publicKey?',
            'secret_'.config('app.name')
        );
        $keys = $rsa->createKey(4096);

        list($publicKey, $privateKey) = [
            Api::keyPath('oauth-public.key', $name),
            Api::keyPath('oauth-private.key', $name),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $this->option('force')) {
            return $this->error('Encryption keys already exist. Use the --force option to overwrite them.');
        }

        file_put_contents($publicKey, Arr::get($keys, 'publickey'));
        file_put_contents($privateKey, Arr::get($keys, 'privatekey'));

        $this->info('Encryption keys generated successfully.');
    }
}
