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
        $userIdentify = $userEntity->getIdentifier();
        return new AccessToken($userIdentify, $clientEntity, $scopes);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        ($this->tokenModel)::create([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => $this->formatScopes($accessTokenEntity->getScopes()),
            'revoked' => false,
            'created_at' => new \DateTime,
            'updated_at' => new \DateTime,
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        ]);
    }

    protected function formatScopes(array $scopes)
    {
        return json_encode($scopes);
    }

    public function revokeAccessToken($tokenId)
    {
        ($this->tokenModel)::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        ($this->tokenModel)::where('id', $tokenId)->where('revoked', 1)->exists();
    }

    public function find($tokenId)
    {
        return ($this->tokenModel)::where('id', $tokenId)->where('revoked', false)->first();
    }
}