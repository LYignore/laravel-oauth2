<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;

interface AccessTokenRepositoryInterface
{
    public function getNewAccessToken(ClientEntityInterface $clientEntity, array $scopes, UserEntityInterface $userEntity);

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity);
}