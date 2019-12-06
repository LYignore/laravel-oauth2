<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AuthCodeEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;

class AuthCode implements AuthCodeEntityInterface
{
    protected $identifier;

    protected $scopes = [];

    protected $expiryDateTime;

    protected $userIdentifier;

    protected $client;

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setExpiryDateTime(\DateTime $dateTime)
    {
        $this->expiryDateTime = $dateTime;
    }

    public function getExpiryDateTime()
    {
        return $this->expiryDateTime;
    }


    public function setClient(ClientEntityInterface $client)
    {
        $this->client = $client;
    }


    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function addScope(ScopeEntityInterface $scope)
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    public function setScopes(array $scopes)
    {
        foreach ($scopes as $value){
            $this->addScope($value);
        }
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

    public function setRedirectUri($uri)
    {
        return new \Exception('This token does not implement this functionality');
    }

    public function getRedirectUri()
    {
        return new \Exception('This token does not implement this functionality');
    }
}