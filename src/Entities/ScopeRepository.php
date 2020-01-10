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

    public function getScopeEntityByIdentifier($identifier, $formate = false)
    {
        $scopeDetail = $this->scopeModel->where('id', $identifier)->where('revoked', false)->first();
        if(!$scopeDetail){
            throw new \Exception('The resource of scope does not exist');
        }
        $uri = $scopeDetail['uri']??"";
        $description = $scopeDetail['description']??"";
        $this->scope = new Scope($identifier, $uri, $description);
        if($formate){
            return $scopeDetail;
        }else{
            return $this->scope;
        }
    }

    public function getNewScope($identifier)
    {
        $scopeDetail = $this->scopeModel->where('id', $identifier)->where('revoked', false)->exists();
        if($scopeDetail){
            throw new \Exception('The token name repeats');
        }
        return new Scope($identifier);
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

    public function finalizeScopes(array $scopes, $grantType)
    {
        return $this->scopesToArray($scopes);
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

    public function getAllScopes($scopes)
    {
        $scopesArr = [];
        foreach ($scopes as $value){
            $scopesArr[] = new Scope($value->id, $value->uri, $value->description);
        }
        return $scopesArr;
    }
}
