<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Repositories\ScopeRepositoryInterface;
use Lyignore\LaravelOauth2\Models\Scope as ScopeModel;

class ScopeRepository implements ScopeRepositoryInterface
{
    protected $scope;
    protected $scopeModel;

    public function __construct(ScopeModel $scope)
    {
        $this->scopeModel = $scope;
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        $scopeDetail = $this->scopeModel->where('id', $identifier)->first();
        if(!$scopeDetail){
            throw new \Exception('The resource of scope does not exist');
        }
        $this->scope = new Scope($identifier);
        return $this->scope->getIdentifier();
    }

    public function persistNewScope(Scope $scope)
    {
        $this->scopeModel->create([
            'id'            => $scope->getIdentifier(),
            'uri'           => $scope->getUri(),
            'description'   => $scope->getDescription(),
            'revoked'       => false
        ]);
    }

    public function finalizeScopes(array $scopes)
    {
        return json_encode($this->scopesToArray($scopes));
    }

    public function scopesToArray(array $scopes)
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }

    public function revokeScope($identify)
    {
        $this->scopeModel->where('id', $identify)->update(['revoked' => true]);
    }

    public function isAccessTokenRevoked($identify)
    {
        $this->scopeModel->where('id', $identify)->where('revoked', 1)->exists();
    }
}
