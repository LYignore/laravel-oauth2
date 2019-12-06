<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface RefreshTokenEntityInterface
{
    public function setIdentifier($str);
    public function getIdentifier();

    public function setExpiryDateTime(\DateTime $dateTime);
    public function getExpiryDateTime();

    public function setClient(ClientEntityInterface $clientEntity);
    public function getClient();

    public function addScope(ScopeEntityInterface $scopesEntity);
    public function getScope();

    public function setAccessToken(AccessTokenEntityInterface $accessTokenEntity);
    public function getAccessToken();
}