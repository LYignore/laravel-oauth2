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
}