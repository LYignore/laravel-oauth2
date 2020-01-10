<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AuthCodeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ScopeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

interface GrantTypeInterface
{
    public function getIdentifier();

    public function setRefreshTokenTTL(\DateInterval $refreshTokenTTL);

    public function setClientRepository(ClientRepositoryInterface $clientRepository);

    public function setUserRepository(UserRepositoryInterface $userRepository);

    public function setAuthCodeRepository(AuthCodeRepositoryInterface $authCodeRepository);

    public function setScopeRepository(ScopeRepositoryInterface $scopeRepository);

    public function setAccessTokenRepository(AccessTokenRepositoryInterface $accessTokenRepository);

    public function setPrivateKey(CryptKey $cryptKey);

    public function setEncryptionKey($key=null);

    public function canRespondToAuthorizationRequest(Request $request);

    public function canRespondToAccessTokenRequest(Request $request);

    public function respondToAccessTokenRequest(Request $request, ResponseTypeInterface $responseType, \DateInterval $dateInterval);

    public function issueAccessToken($clientIdentifier, UserEntityInterface $userEntity, \DateInterval $dateInterval, array $scopes);

    public function validateClient(Request $request);

    public function validateAuthorizationRequest(Request $request);

    public function issueRefreshToken(AccessTokenEntityInterface $accessTokenEntity);
}