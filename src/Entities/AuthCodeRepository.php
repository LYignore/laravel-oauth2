<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\AuthCodeEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AuthCodeRepositoryInterface;
use Lyignore\LaravelOauth2\Models\AuthCode as AuthCodeModel;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    protected $authCodeModel;

    public function __construct(AuthCodeModel $authCode)
    {
        $this->authCodeModel = $authCode;
    }

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

        $this->authCodeModel->create($attributes);
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

    public function revokeAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $codeId = $authCodeEntity->getIdentifier();
        $this->authCodeModel->where('id', $codeId)->update(['revoked' => true]);
    }

    public function isAuthCodeRevoked(AuthCodeEntityInterface $authCodeEntity)
    {
        $codeId = $authCodeEntity->getIdentifier();
        return $this->authCodeModel->where('id', $codeId)->where('revoked', 1)->exists();
    }
}
