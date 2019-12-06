<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class ClientCredentialsGrant extends AbstractGrant
{
    public function getIdentifier()
    {
        return 'client_credentials';
    }

    public function respondToAccessTokenRequest(Request $request, ResponseTypeInterface $responseType, \DateInterval $dateInterval)
    {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($request);

        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier());
        $accessToken = $this->issueAccessToken($client->getIdentifier(), null, $dateInterval, $finalizedScopes);

        $responseType->setAccessToken($accessToken);

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