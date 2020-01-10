<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface ClientEntityInterface
{
    public function setIdentifier($identify);

    public function getIdentifier();

    public function setSecret($secret);

    public function getSecret();

    public function setName($name);

    public function getName();

    public function setRedirectUri($uri);

    public function getRedirectUri();

    public function setGrantType($grantType);

    public function getGrantType();

    public function setPrivateKey($privateKey);

    public function getPrivateKey();

    public function setPublicKey($publicKey);

    public function getPublicKey();

    public function setScopes($scopes);

    public function getScopes();

    public function setVaildUntil(\DateTime $dateTime);

    public function getVaildUntil();
}