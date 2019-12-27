<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
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


    public function getClientEntity($identifier, $grantType)
    {
        $record = $this->clientModel->findActive($identifier);

        if(!$record || $this->handlesGrant($record, $grantType)){
            return;
        }

        $this->client = new Client($identifier);
        $this->client->setName($record->name);
        $this->client->setRedirectUri($record->redirect);
        $this->client->setSecret($record->secret);

        return $this->client;
    }

    protected function handlesGrant($record, $grantType)
    {
        switch ($grantType){
            case 'authorization_code':
                return !$record->firstParty();
            case 'credentials':
                return $record->credentials_client;
            case 'password':
                return $record->password_client;
            default:
                return true;
        }
    }

    protected function generateUniqueIdentifier($length = 40)
    {
        try{
            return bin2hex(random_bytes($length));
        }catch (\Exception $e){
            throw new \Exception($e);
        }
    }

    /**
     * Generate a client instance
     * @param String|null $identify
     * @return \Lyignore\LaravelOauth2\Entities\Client
     */
    public function getNewClient($identify = null)
    {
        if(is_null($identify)){
            $id = (string)time();
            $identifyLast = $this->generateUniqueIdentifier(7);
            $identify = $id.$identifyLast;
        }
        $client= new Client($identify);
        $secret = $this->generateUniqueIdentifier(32);
        $client->setSecret($secret);
        return $client;
    }


    /**
     * Stores the newly generated client instance
     * @param \Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface $clientEntity
     * @return void
     */
    public function persistNewClient(ClientEntityInterface $clientEntity)
    {
        return $this->clientModel->create([
            'id'    => $clientEntity->getIdentifier(),
            'name'  => $clientEntity->getName(),
            'secret'=> $clientEntity->getSecret(),
            'redirect' => $clientEntity->getRedirectUri(),
            'credentials_client'    => true,
            'password_client'       => false,
            'authorization_client'  => false,
            'revoked'               => false
        ]);
    }
}