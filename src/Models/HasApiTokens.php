<?php
namespace Lyignore\LaravelOauth2\Models;

trait HasApiTokens
{
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function retrieveById($identify)
    {
        return self::where('id', $identify)->first();
    }
}
