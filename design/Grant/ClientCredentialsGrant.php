<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;
use Lyignore\LaravelOauth2\Entities\UserRepository;
use Lyignore\LaravelOauth2\Models\Scope;

class ClientCredentialsGrant extends AbstractGrant
{
    const IDENTIFIER = 'credentials_client';

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
        $defaultScopes = $client->getScopes();
        $scopes = $this->validateScopes($request, $defaultScopes);
        // Verify that the client has token call permissions corresponding to scope

        //$finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier());
        $userEntity = $this->getDefaultUserEntity($client->getIdentifier());
        $accessToken = $this->issueAccessToken($client->getIdentifier(), $userEntity, $dateInterval, $scopes);

        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    protected function getDefaultUserEntity($identify)
    {
        $userRepository = new UserRepository();
        return $userRepository->getUserEntityByUserCrentials($identify, 1, ClientCredentialsGrant::IDENTIFIER);
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
     * The resource collection is returned by the scope field on the accesstoken application
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateScopes(Request $request, array $defaultScopes)
    {
        $scopes = $request->input('scope');
        $scopeList = array_filter(explode(self::SCOPE_DELIMITER_STRING, trim($scopes)));
        $scopeList = empty($scopeList)?$defaultScopes:$scopeList;
        $checkScope = array_intersect($defaultScopes, $scopeList);
        if($checkScope != $scopeList&&$scopes!='*'){
            throw new \Exception('The client does not have access to this token resource');
        }
        if($scopes == '*'){
            $scopesModel = Scope::whereIn('id', $defaultScopes)->get();
            return $this->scopeRepository->getAllScopes($scopesModel);
        }
        foreach ($checkScope as $scopeItem){
            if($scopeItem == '*'){
                $scopesModel = Scope::whereIn('id', $defaultScopes)->get();
                return $this->scopeRepository->getAllScopes($scopesModel);
            }
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);
            if(!$scope instanceof ScopeEntityInterface){
                throw new \Exception('Scope type error');
            }
            $validScopes[] = $scope;
        }
        return $validScopes;
    }
}
