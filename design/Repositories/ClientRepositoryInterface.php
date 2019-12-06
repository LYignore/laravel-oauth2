<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

interface ClientRepositoryInterface
{
    public function getClientEntity($identifier, $grantType, $clientSecret = null);
}