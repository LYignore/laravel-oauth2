<?php
namespace Lyignore\LaravelOauth2\Design\ResponseTypes;

use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;

abstract class AbstractResponse implements ResponseTypeInterface
{
    use CryptTrait;

    protected $accessToken;

    protected $refreshToken;

    protected $privateKey;

    public function setAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->accessToken = $accessTokenEntity;
    }

    public function setRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->refreshToken = $refreshTokenEntity;
    }

    public function setPrivateKey(CryptKey $key)
    {
        $this->privateKey = $key;
    }
}