<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;

interface ClientRepositoryInterface
{
    public function getClientEntity($identifier, $grantType);

    public function persistNewClient(ClientEntityInterface $clientEntity);

    public function getNewClient();
}