<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;

interface RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken(AccessTokenEntityInterface $tokenEntity);

    public function persistRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity);
}