<?php
namespace Lyignore\LaravelOauth2\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Scope implements ScopeEntityInterface
{
    use EntityTrait;

    public function __construct($identifier)
    {
        $this->setIdentifier($identifier);
    }

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}