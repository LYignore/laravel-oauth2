<?php
namespace Lyignore\LaravelOauth2\Entities;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class User implements UserEntityInterface
{
    use EntityTrait;

    public function __construct($id)
    {
        $this->setIdentifier($id);
    }
}