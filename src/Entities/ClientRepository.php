<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Api;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\AuthCodeGrant;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;
use Lyignore\LaravelOauth2\Design\Grant\PasswordGrant;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Models\Client as ClientModel;

class ClientRepository implements ClientRepositoryInterface
{
    use CryptTrait;
    protected $client;

    protected $clientModel;

    public function __construct(ClientModel $client)
    {
        $this->clientModel = $client;
    }


    public function getClientEntity($identifier, $grantType)
    {
        $record = $this->clientModel->findActive($identifier);
        if(!$record || !$this->handlesGrant($record, $grantType)){
            return;
        }

        $this->client = new Client();
        $this->client->setIdentifier($identifier);
        $this->client->setName($record->name);
        $this->client->setRedirectUri($record->redirect);
        $this->client->setSecret($record->secret);
        $this->client->setGrantType($grantType);
        $scopes = json_decode($record->scopes, true);
        $this->client->setScopes($scopes);
//        $publicKey = Api::keyPath('oauth-public.key', 'secret_'.$record->name);
//        $privateKey = Api::keyPath('oauth-private.key', 'secret_'.$record->name);
        $privateKey = $this->makeCryptKey('oauth-private.key', 'secret_'.$this->client->getName());
        $this->client->setPrivateKey($privateKey);
        $publicKey = $this->makeCryptKey('oauth-public.key', 'secret_'.$this->client->getName());
        $this->client->setPublicKey($publicKey);
        return $this->client;
    }

    public function revokeClient($tokenId)
    {
        ($this->tokenModel)::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isClientRevoked($clientId)
    {
        return ($this->clientModel)::where('id', $clientId)->where('revoked', 1)->exists();
    }

    protected function handlesGrant($record, $grantType)
    {
        switch ($grantType){
            case AuthCodeGrant::IDENTIFIER:
                return $record->authorization_client;
            case ClientCredentialsGrant::IDENTIFIER:
                return $record->credentials_client;
            case PasswordGrant::IDENTIFIER:
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
            $identifyLast = $this->generateUniqueIdentifier(16);
            $identify = 'akk'.$identifyLast;
        }
        $client= new Client();
        $client->setIdentifier($identify);
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
            'scopes'   => $clientEntity->getScopes(),
            'credentials_client'    => ($clientEntity->getGrantType() == 'credentials_client')?:false,
            'password_client'       => ($clientEntity->getGrantType() == 'password_client')?:false,
            'authorization_client'  => ($clientEntity->getGrantType() == 'authorization_client')?:false,
            'revoked'               => false,
            'valid_at'              => $clientEntity->getVaildUntil(),
        ]);
    }

    /**
     * Gets the data table information corresponding to the client
     * @params string $identify
     * @return \Lyignore\LaravelOauth2\Models\Client
     */
    public function retrieveById($identify)
    {
        return $this->clientModel->where('id', $identify)->first();
    }
}