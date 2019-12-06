<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\RefreshTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Models\Refresh;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    protected $refreshTokenModel;

    public function __construct(Refresh $refresh)
    {
        $this->refreshTokenModel = $refresh;
    }

    public function getNewRefreshToken(AccessTokenEntityInterface $tokenEntity)
    {
        return new RefreshToken($tokenEntity);
    }

    public function persistRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        ($this->refreshTokenModel)::create([
            'id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);
    }

    public function revokeRefreshToken($tokenId)
    {
        ($this->refreshTokenModel)::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        ($this->refreshTokenModel)::where('id', $tokenId)->where('revoked', 1)->exists();
    }
}