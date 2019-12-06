<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Models\Token;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    protected $tokenModel;

    public function __construct(Token $token)
    {
        $this->tokenModel = $token;
    }

    public function getNewAccessToken(ClientEntityInterface $clientEntity, array $scopes, UserEntityInterface $userEntity)
    {
        return new AccessToken($userEntity, $clientEntity, $scopes);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        ($this->tokenModel)::create([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => $this->scopesToArray($accessTokenEntity->getScopes()),
            'revoked' => false,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        ]);
    }

    public function revokeAccessToken($tokenId)
    {
        ($this->tokenModel)::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        ($this->tokenModel)::where('id', $tokenId)->where('revoked', 1)->exists();
    }
}