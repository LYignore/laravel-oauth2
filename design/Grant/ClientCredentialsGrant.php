<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class ClientCredentialsGrant extends AbstractGrant
{
    const IDENTIFIER = 'client_credentials';

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
    public function respondToAccessTokenRequest(Request $request, ResponseTypeInterface $responseType, \DateInterval $dateInterval)
    {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($request);

        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier());
        $accessToken = $this->issueAccessToken($client->getIdentifier(), null, $dateInterval, $finalizedScopes);

        $responseType->setAccessToken($accessToken);

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

    /**
     * The resource collection is returned by the scope field on the accesstoken application
     * @param \Illuminate\Http\Request $request
     * @return array
     */
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
