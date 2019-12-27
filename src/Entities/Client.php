<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    protected $name;

    protected $redirectUri = 'http://loacalhost';

    protected $identifier;

    protected $secret;

    public function setIdentifer($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
}