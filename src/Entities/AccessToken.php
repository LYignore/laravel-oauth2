<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;

class AccessToken implements AccessTokenEntityInterface
{
    use CryptTrait;
    protected $identifier;

    protected $scopes = [];

    protected $expiryDateTime;

    protected $userIdentifier;

    protected $client;

    public function __construct($userIdentifier, $clientIdentifier, array $scopes=[])
    {
        $this->setUserIdentifier($userIdentifier);
        $this->setClient($clientIdentifier);
        foreach ($scopes as $scope){
            $this->addScope($scope);
        }
    }

    public function setIdentifier($str)
    {
        $this->identifier = $str;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setExpiryDateTime(\DateTime $dateTime)
    {
        $this->expiryDateTime = $dateTime;
    }

    public function getExpiryDateTime()
    {
        return $this->expiryDateTime;
    }

    public function setClient(ClientEntityInterface $clientEntity)
    {
        $this->client = $clientEntity;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function addScope(ScopeEntityInterface $scope)
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    public function getScope()
    {
        return array_values($this->scopes);
    }

    public function setUserIdentifier($identifier)
    {
        $this->userIdentifier = $identifier;
    }

    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    public function convertToJWT(CryptKey $cryptKey)
    {
        return (new Builder())
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('scopes', $this->getScope())
            ->sign(new Sha256(), new Key($cryptKey->getKeyPath(), $cryptKey->getPassPhrase()))
            ->getToken();
    }
}