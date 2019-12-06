<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface UserRepositoryInterface
{
    public function getUserEntityByUserCrentials(
        $username, $password, $grantType
    );
}