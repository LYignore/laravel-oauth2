<?php

namespace Lyignore\LaravelOauth2\Console;

use Illuminate\Console\Command;
use Lyignore\LaravelOauth2\Entities\ClientRepository;

class ClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:client
            {--credentials : Create a credentials access token client}
            {--password : Create a password grant client}
            {--name= : The name of the client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a client for issuing access tokens';

    /**
     * Execute the console command.
     *
     * @param  \Lyignore\LaravelOauth2\Entities\ClientRepository  $clients
     * @return void
     */
    public function handle(ClientRepository $clients)
    {
        if ($this->option('credentials')) {
            return $this->createCredentialsClient($clients);
        }

        if ($this->option('password')) {
            return $this->createPasswordClient($clients);
        }

        $this->createAuthCodeClient($clients);
    }

    protected function createCredentialsClient(ClientRepository $clientRepository)
    {
        $name = $this->option('name') ?: $this->ask(
            'What should we name the credentials client grant client?',
            config('app.name')
        );

        $uri = config('app.url');

        $client = $clientRepository->getNewClient();
        $client->setName($name);
        $client->setRedirectUri($uri);

        $this->info('credentials grant client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }

    /**
     * Create a new password grant client.
     *
     * @param  \Laravel\Passport\ClientRepository  $clients
     * @return void
     */
    protected function createPasswordClient(ClientRepository $clientRepository)
    {
        $name = $this->option('name') ?: $this->ask(
            'What should we name the password grant client?',
            config('app.name')
        );

        $uri = $this->option('uri')?: $this->ask(
            'What client address fot client?',
            config('app.url')
        );

        $client = $clientRepository->getNewClient();
        $client->setName($name);
        $client->setRedirectUri($uri);

        $this->info('Password grant client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }

    /**
     * Create a authorization code client.
     *
     * @param  \Laravel\Passport\ClientRepository  $clientRepository
     * @return void
     */
    protected function createAuthCodeClient(ClientRepository $clientRepository)
    {
        $userId = $this->ask(
            'Which user ID should the client be assigned to?'
        );

        $name = $this->option('name') ?: $this->ask(
            'What should we name the client?'
        );

        $redirect = $this->ask(
            'Where should we redirect the request after authorization?',
            url('/auth/callback')
        );

        $client = $clientRepository->getNewClient();
        $client->setName($name);
        $client->setRedirectUri($redirect);

        $this->info('New client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }
}
