<?php
namespace Lyignore\LaravelOauth2\Design\Entities;

interface ClientEntityInterface
{
    public function getIdentifier();

    public function getName();

    public function getRedirectUri();
}