<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\ScopeEntityInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AuthCodeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ScopeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class AuthCodeGrant extends AbstractGrant
{
    const IDENTIFIER = 'authorization_client';
    private $authCodeTTL;

    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        ScopeRepositoryInterface $scopeRepository,
        string $encryptKey,
        \DateInterval $authCodeTTL
    )
    {
        $this->setAuthCodeRepository($authCodeRepository);
        $this->setScopeRepository($scopeRepository);
        $this->setEncryptionKey($encryptKey);
        $this->setRefreshTokenTTL(new \DateInterval('P7D'));
        $this->authCodeTTL = $authCodeTTL;
    }

    public function getIdentifier()
    {
        return 'authorization_code';
    }

    public function makeRedirectUri($uri, $params = [], $queryDelimiter = '?')
    {
        $uri .= (strstr($uri, $queryDelimiter) === false) ? $queryDelimiter : '&';

        return $uri . http_build_query($params);

    }

    public function canRespondToAuthorizationRequest(Request $request)
    {
        return (
            array_key_exists('response_type', $request->all())
            && $request->input('response_type') == 'code'
            && isset($request->client_id)
        );
    }

    public function respondToAccessTokenRequest(Request $request, ResponseTypeInterface $responseType, \DateInterval $accessTokenTTL)
    {
        try{
            $authCodeEntity = $this->validateAuthorizationRequest($request);
            $scopes = $this->scopeRepository->finalizeScopes(
                $this->validateScopes($authCodeEntity->getScopes()),
                $this->getIdentifier()
            );
            $client = $this->validateClient($request);
            $user = $authCodeEntity->getUserIdentifier();
            $accessToken = $this->issueAccessToken($client, $user, $accessTokenTTL, $scopes);
            $refreshToken= $this->issueRefreshToken($accessToken);
            $responseType->setAccessToken($accessToken);
            $responseType->setRefreshToken($refreshToken);
            $this->authCodeRepository->revokeAuthCode($authCodeEntity);
            return $responseType;
        }catch (\Exception $e){
            throw new \Exception('Cannot decrypt the authorization code');
        }
    }

    public function validateScopes($scopes)
    {
        if (!\is_array($scopes)) {
            $scopes = $this->convertScopesQueryStringToArray($scopes);
        }
        $validScopes = [];

        foreach ($scopes as $scopeItem) {
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);

            if ($scope instanceof ScopeEntityInterface === false) {
                throw new \Exception('Scope type error');
            }

            $validScopes[] = $scope->getIdentifier();
        }

        return $validScopes;
    }

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

    public function validateAuthorizationRequest(Request $request)
    {
        $client = $this->validateClient($request);
        $encryptedAuthCode = $request->input('code',null);
        if($encryptedAuthCode==null){
            throw new \Exception('The request is missing the code parameter');
        }
        $authCodeEntity = $this->decrypt($encryptedAuthCode);
        if(time() > $authCodeEntity->getExpiryDateTime()){
            throw new \Exception('Authorization code has expired');
        }

        if($this->authCodeRepository->isAuthCodeRevoked($authCodeEntity->getIdentifier()) == true){
            throw new \Exception('Authorization code has been revoked');
        }

        if($authCodeEntity->getClient() != $client->getIdentifier()){
            throw new \Exception('Authorization code was not issued to this client');
        }

        $redirectUri = $request->input('reduire_uri', null);
        if($authCodeEntity->getRedirectUri() != $redirectUri){
            throw new \Exception('Invalid redirect URI');
        }
        return $authCodeEntity;
    }
}