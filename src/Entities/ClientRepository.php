<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Models\Client as ClientModel;

class ClientRepository implements ClientRepositoryInterface
{
    protected $client;

    protected $clientModel;

    public function __construct(ClientModel $client)
    {
        $this->clientModel = $client;
    }


    public function getClientEntity($identifier, $grantType, $clientSecret = null)
    {
        $record = $this->clientModel->findActive($identifier);

        if(!$record || $this->handlesGrant($record, $grantType)){
            return;
        }

        $this->client = new Client($identifier, $record->name, $record->redirect);

        return $this->client;
    }

    protected function handlesGrant($record, $grantType)
    {
        switch ($grantType){
            case 'authorization_code':
                return !$record->firstParty();
            case 'personal_access':
                return $record->personal_access_client;
            case 'password':
                return $record->password_client;
            default:
                return true;
        }
    }
}