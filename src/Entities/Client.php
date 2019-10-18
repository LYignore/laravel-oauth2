<?php
namespace Lyignore\LaravelOauth2\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client implements ClientEntityInterface
{
    use ClientTrait, EntityTrait;

    public function __construct($identifier, $name, $redirectUri)
    {
        $this->setIdentifier($identifier);

        $this->name = $name;
        $this->redirectUri = explode(",", $redirectUri);
    }

}