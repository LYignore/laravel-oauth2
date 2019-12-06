<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AuthCodeEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $attributes = [
            'id' => $authCodeEntity->getIdentifier(),
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
            'scopes' => $this->formatScopesForStorage($authCodeEntity->getScopes()),
            'revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
        ];

        Passport::authCode()->setRawAttributes($attributes)->save();
    }



    public function formatScopesForStorage(array $scopes)
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