<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface AuthCodeEntityInterface
{
    public function setIdentifier($str);
    public function getIdentifier();

    public function setExpiryDateTime(\DateTime $dateTime);
    public function getExpiryDateTime();

    public function setClient(ClientEntityInterface $client);
    public function getClient();

    public function setRedirectUri($uri);
    public function getRedirectUri();

    public function setScopes(array $scopes);
    public function getScope();

    public function getUserIdentifier();
}