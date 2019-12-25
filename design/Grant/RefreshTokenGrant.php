<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use DateInterval;
use Exception;
use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Repositories\RefreshTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class RefreshTokenGrant extends AbstractGrant
{
    const IDENTIFIER = 'refresh_token';
    /**
     * Determine whether there is a user role and whether there is a refreshtoken requirement
     */
    public function __construct(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->setRefreshTokenRepository($refreshTokenRepository);

        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * Generate token requests in response
     * @param \Illuminate\Http\Request $request
     * @param \Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface $responseType
     * @param \DateInterval $dateInterval
     * @return \Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface
     */
    public function respondToAccessTokenRequest(
        Request $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        $client = $this->validateClient($request);
        $oldRefreshToken = $this->validateOldRefreshToken($request, $client->getIdentifier());
        $scopes = $this->validateScopes($this->getRequestParameter(
            'scope',
            $request,
            implode(self::SCOPE_DELIMITER_STRING, $oldRefreshToken['scopes']))
        );

        foreach ($scopes as $scope) {
            if (in_array($scope->getIdentifier(), $oldRefreshToken['scopes'], true) === false) {
                throw new Exception(json_encode($scope->getIdentifier()));
            }
        }

        $this->accessTokenRepository->revokeAccessToken($oldRefreshToken['access_token_id']);
        $this->refreshTokenRepository->revokeRefreshToken($oldRefreshToken['refresh_token_id']);

        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $oldRefreshToken['user_id'], $scopes);

        $responseType->setAccessToken($accessToken);

        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    protected function validateOldRefreshToken(Request $request, $clientId)
    {
        $encryptedRefreshToken = $this->getRequestParameter('refresh_token', $request);
        if (is_null($encryptedRefreshToken)) {
            throw new Exception();
        }

        try {
            $refreshToken = $this->decrypt($encryptedRefreshToken);
        } catch (\Exception $e) {
            throw new \Exception('Cannot decrypt the refresh token');
        }

        $refreshTokenData = json_decode($refreshToken, true);
        if ($refreshTokenData['client_id'] !== $clientId) {
            throw new Exception();
        }

        if ($refreshTokenData['expire_time'] < time()) {
            throw new Exception('Token has expired');
        }

        if ($this->refreshTokenRepository->isRefreshTokenRevoked($refreshTokenData['refresh_token_id']) === true) {
            throw new Exception('Token has been revoked');
        }

        return $refreshTokenData;
    }

    public function canRespondToAuthorizationRequest(Request $request)
    {
        return false;
    }

    public function validateAuthorizationRequest(Request $request)
    {
        throw new \Exception('This refreshGrant cannot validate an authorization request');
    }
}
