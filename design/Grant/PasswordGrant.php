<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class PasswordGrant extends AbstractGrant
{
    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenTTL(new \DateInterval('P7D'));
    }

    public function getIdentifier()
    {
        return 'password';
    }

    public function respondToAccessTokenRequest(
        Request $request,
        ResponseTypeInterface $responseType,
        \DateInterval $dateInterval)
    {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($request);
        $user = $this->validateUser($request);

        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier());

        $accessToken = $this->issueAccessToken($client->getIdentifier(), $user, $dateInterval, $finalizedScopes);
        $refreshToken= $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    public function canRespondToAuthorizationRequest(Request $request)
    {
        return false;
    }

    public function validateAuthorizationRequest(Request $request)
    {
        throw new \Exception('This grant cannot validate an authorization request');
    }

    public function validateClient(Request $request)
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        if(empty($clientId)){
            throw new \Exception('Failed to get clientID');
        }

        if(!$this->clientRepository instanceof ClientRepositoryInterface){
            throw new \Exception('Not configured setClientRepository');
        }

        $client =$this->clientRepository->getClientEntity(
            $clientId, $this->getIdentifier(), $clientSecret
        );

        return $client;
    }

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

    public function validateScopes(Request $request)
    {
        $scopes = $request->input('scope', $this->defaultScope());
        $scopeList = array_filter(explode(self::SCOPE_DELIMITER_STRING, trim($scopes)));
        foreach ($scopeList as $scopeItem){
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);

            if(!$scope instanceof ScopeEntityInterface){
                throw new \Exception('Scope type error');
            }
            $validScopes[] = $scope;
        }
        return $validScopes;
    }
}