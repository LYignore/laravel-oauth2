<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;

class RefreshToken implements RefreshTokenEntityInterface
{
    use CryptTrait;
    protected $identifier;

    protected $scopes = [];

    protected $expiryDateTime;

    protected $userIdentifier;

    protected $client;

    protected $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->setAccessToken($accessToken);
    }

    public function setIdentifier($str)
    {
        $this->identifier = $str;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }


    public function setClient(ClientEntityInterface $clientEntity)
    {
        $this->client = $clientEntity;
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

    public function setAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->accessToken = $accessTokenEntity;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }
}