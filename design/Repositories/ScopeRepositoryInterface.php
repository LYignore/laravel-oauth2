<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

use Lyignore\LaravelOauth2\Entities\Scope;

interface ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier, $formate = false);

    public function getNewScope($identifier);

    public function persistNewScope(Scope $scope);

    public function finalizeScopes(array $scopes, $grantType);
}
