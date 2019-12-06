<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;

class Scope implements ScopeEntityInterface
{
    protected $identifier;

    public function __construct($name)
    {
        $this->identifier = $name;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}