<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\RefreshTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class PasswordGrant extends AbstractGrant
{
    const IDENTIFIER = 'password';

    /**
     * Determine whether there is a user role and whether there is a refreshtoken requirement
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        $dateInterval=null
    )
    {
        $this->setUserRepository($userRepository);

        $this->setRefreshTokenRepository($refreshTokenRepository);
        if($dateInterval instanceof \DateInterval){
            $this->setRefreshTokenTTL($dateInterval);
        }else{
            $this->setRefreshTokenTTL(new \DateInterval('P7D'));
        }
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function setRefreshTokenRepository(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
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
        \DateInterval $dateInterval)
    {
        $client = $this->validateClient($request);
        $defaultScopes = $client->getScopes();
        $scopes = $this->validateScopes($request, $defaultScopes);
        $user = $this->validateUser($request);

        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier());

        $accessToken = $this->issueAccessToken($client->getIdentifier(), $user, $dateInterval, $finalizedScopes);
        $refreshToken= $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * Determine whether an authorization request can be responded to
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function canRespondToAuthorizationRequest(Request $request)
    {
        return false;
    }

    /**
     * Verify that the authorization note is valid
     * @param \Illuminate\Http\Request $request
     * @return code|null
     */
    public function validateAuthorizationRequest(Request $request)
    {
        throw new \Exception('This grant cannot validate an authorization request');
    }

    /**
     * Verify that the client is valid when you request accesstoken
     * @param \Illuminate\Http\Request $request
     * @return \Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface
     */
    public function validateClient(Request $request)
    {
        $clientId = $request->input('client_id');

        if(empty($clientId)){
            throw new \Exception('Failed to get clientID');
        }

        if(!$this->clientRepository instanceof ClientRepositoryInterface){
            throw new \Exception('Not configured setClientRepository');
        }

        $client =$this->clientRepository->getClientEntity(
            $clientId, $this->getIdentifier()
        );

        return $client;
    }

    /**
     * Verify that the user is valid when you request accesstoken
     * @param \Illuminate\Http\Request $request
     * @return \Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface
     */
    public function validateUser(Request $request)
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        if(empty($username) || empty($password)){
            throw new \Exception('User information error');
        }

        $user = $this->userRepository->getUserEntityByUserCrentials(
            $username, $password, $this->getIdentifier()
        );

        return $user;
    }

    /**
     * The resource collection is returned by the scope field on the accesstoken application
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateScopes(Request $request, array $defaultScopes)
    {
        $scopes = $request->input('scope', $defaultScopes);
        $scopeList = array_filter(explode(self::SCOPE_DELIMITER_STRING, trim($scopes)));
        foreach ($scopeList as $scopeItem){
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);

            if(!$scope instanceof ScopeEntityInterface){
                throw new \Exception('Scope type error');
            }
            $validScopes[] = $scope->getIdentifier();
        }
        return $validScopes;
    }
}
