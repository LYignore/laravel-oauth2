<?php
namespace Lyignore\LaravelOauth2\Design\Repositories;

use Lyignore\LaravelOauth2\Design\Entities\AuthCodeEntityInterface;

interface AuthCodeRepositoryInterface
{
    public function getNewAuthCode();

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity);

    public function revokeAuthCode(AuthCodeEntityInterface $authCodeEntity);

    public function isAuthCodeRevoked(AuthCodeEntityInterface $authCodeEntity);
}