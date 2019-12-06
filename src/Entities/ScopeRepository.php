<?php
namespace Lyignore\LaravelOauth2\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    protected $scope;
    public function getScopeEntityByIdentifier($identifier)
    {
        $this->scope = new Scope($identifier);
        return $this->scope->getIdentifier();
    }

    public function finalizeScopes(array $scopes,
                                   $grantType,
                                   ClientEntityInterface $clientEntity,
                                   $userIdentifier = null)
    {
        return json_encode($this->scopesToArray($scopes));
    }

    public function scopesToArray(array $scopes)
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }
}