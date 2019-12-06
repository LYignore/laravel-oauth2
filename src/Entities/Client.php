<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    protected $name;

    protected $redirectUri;

    protected $identifier;

    public function __construct($identifier, $name, $redirectUri)
    {
        $this->setIdentifer($identifier);

        $this->name = $name;
        $this->redirectUri = $redirectUri;
    }

    public function setIdentifer($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
}