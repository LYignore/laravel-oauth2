<?php
namespace Lyignore\LaravelOauth2\Entities;

use Laravel\Pass
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    protected $clients;

    public function __construct(Client $clients)
    {
        $this->clients = $clients;
    }

    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        // TODO: Implement getClientEntity() method.
    }
}