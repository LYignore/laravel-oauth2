<?php

namespace Lyignore\LaravelOauth2\Console;

use Illuminate\Console\Command;
use Lyignore\LaravelOauth2\Entities\Client;
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
            {--name= : The name of the client}
            {--scopes= : The name of the scope}
            {--uri= : The name of the client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a client for issuing access tokens';

    /**
     * Execute the console command.
     *
     * @param  \Lyignore\LaravelOauth2\Entities\ClientRepository  $clientRepository
     * @return void
     */
    public function handle(ClientRepository $clientRepository)
    {
        try{
            if ($this->option('credentials')) {
                return $this->createCredentialsClient($clientRepository);
            }

            if ($this->option('password')) {
                return $this->createPasswordClient($clientRepository);
            }
            $this->createAuthCodeClient($clientRepository);
        }catch (\Exception $e){
            $this->error($e->getMessage());
            exit;
        }
    }

    protected function createCredentialsClient(ClientRepository $clientRepository)
    {
        $name = $this->option('name') ?: $this->ask(
            'What should we name the credentials client grant client?',
            config('app.name')
        );

        $scopes = $this->option('scopes') ?: $this->ask(
            'What token permission is assigned to the current client?(comma-separated)',
            '*'
        );
        $scopeArr = json_encode(explode(",", $scopes));

        $uri = config('app.url');
        $client = $clientRepository->getNewClient();
        $client->setName($name);
        $client->setRedirectUri($uri);
        $client->setGrantType(Client::CREDENTIALS_CLIENT);
        $client->setScopes($scopeArr);
        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('P3Y'));
        $client->setVaildUntil($dateTime);
        $clientRepository->persistNewClient($client);

        $this->info('credentials grant client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }

    /**
     * Create a new password grant client.
     *
     * @param  \Laravel\Passport\ClientRepository  $clientRepository
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
        $client->setGrantType(Client::PASSWORD_CLIENT);
        $clientRepository->persistNewClient($client);

        $this->info('Password grant client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }

    /**
     * Create a authorization code client.
     *
     * @param  \Lyignore\LaravelOauth2\Entities\ClientRepository $clientRepository
     * @return void
     */
    protected function createAuthCodeClient(ClientRepository $clientRepository)
    {
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
        $client->setGrantType(Client::AUTHORIZATION_CLIENT);
        $clientRepository->persistNewClient($client);

        $this->info('New client created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->getIdentifier());
        $this->line('<comment>Client Secret:</comment> '.$client->getSecret());
    }
}
