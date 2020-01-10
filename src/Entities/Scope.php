<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;

class Scope implements ScopeEntityInterface
{
    protected $identifier;

    protected $uri;

    protected $description;

    public function __construct($identifier, $uri = '', $dec = '')
    {
        $this->identifier   = $identifier;
        $this->uri          = $uri;
        $this->description  = $dec;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setDescription($detail)
    {
        $this->description = $detail;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
