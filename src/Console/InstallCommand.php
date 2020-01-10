<?php

namespace Lyignore\LaravelOauth2\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:install 
            {--force : Overwrite keys if they already exist}
            {--name= : The name of the client}
            {--credentials : Create a credentials access token client}
            {--password : Create a password grant client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the commands necessary to prepare Passport for use';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $password = $this->option('password');

        $this->call('passport:keys', ['--force' => $this->option('force'), '--name' => $this->option('name')]);

        if($password){
            $this->call('passport:client', ['--password' => true, '--name' => $this->option('name'), '--scopes'=>'*']);
        }else{
            $this->call('passport:client', ['--credentials' => true, '--name' => $this->option('name'), '--scopes'=>'*']);
        }
    }
}
