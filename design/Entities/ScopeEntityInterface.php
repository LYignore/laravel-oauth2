<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface ScopeEntityInterface
{
    public function getIdentifier();

    public function getUri();

    public function getDescription();
}
