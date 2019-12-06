<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

interface ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier);

    public function finalizeScopes(array $scopes, $grantType);
}