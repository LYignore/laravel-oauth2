<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface ScopeEntityInterface
{
    public function getIdentifier();

    public function setUri($uri);

    public function getUri();

    public function setDescription($detail);

    public function getDescription();
}
