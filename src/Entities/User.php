<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    protected $identifier;

    public function __construct($idnetifier)
    {
        $this->setIdentifier($idnetifier);
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}