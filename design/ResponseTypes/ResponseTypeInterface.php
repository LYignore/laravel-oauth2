<?php
namespace Lyignore\LaravelOauth2\Design\ResponseTypes;

use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;

interface ResponseTypeInterface
{
    public function setAccessToken(AccessTokenEntityInterface $accessTokenEntity);

    public function setRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity);

    public function generateResponse();
}