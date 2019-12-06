<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

use Lyignore\LaravelOauth2\Design\CryptKey;

interface AccessTokenEntityInterface
{
    public function setIdentifier($str);
    public function getIdentifier();

    public function setExpiryDateTime(\DateTime $dateTime);
    public function getExpiryDateTime();

    public function setClient(ClientEntityInterface $clientEntity);
    public function getClient();

    public function addScope(ScopeEntityInterface $scopesEntity);
    public function getScope();

    public function setUserIdentifier($identifier);
    public function getUserIdentifier();

    public function convertToJWT(CryptKey $cryptKey);
}