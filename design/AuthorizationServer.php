<?php
namespace Lyignore\LaravelOauth2\Design;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\GrantTypeInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ScopeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\ResponseTypes\AbstractResponse;
use Lyignore\LaravelOauth2\Design\ResponseTypes\BearerTokenResponse;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;

class AuthorizationServer
{
    protected $enabledGrantTypes = [];

    protected $encryptionKey;

    protected $privateKey;

    protected $grantTypeAccessTokenTTL = [];

    protected $publicKey;

    protected $responseType;

    protected $clientRepository;

    protected $accessTokenRepository;

    protected $scopeRepository;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $encryptionKey,
        ResponseTypeInterface $responseType=null
    ) {
        $this->clientRepository = $clientRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->scopeRepository = $scopeRepository;

        $this->encryptionKey = $encryptionKey;

        if ($responseType === null) {
            $responseType = new BearerTokenResponse();
        } else {
            $responseType = clone $responseType;
        }

        $this->responseType = $responseType;
    }

    public function enableGrantType(GrantTypeInterface $grantType, \DateInterval $accessTokenTTL=null)
    {
        if ($accessTokenTTL === null) {
            $accessTokenTTL = new \DateInterval('PT1H');
        }

        $grantType->setAccessTokenRepository($this->accessTokenRepository);
        $grantType->setClientRepository($this->clientRepository);
        $grantType->setScopeRepository($this->scopeRepository);
        $grantType->setEncryptionKey($this->encryptionKey);

        $this->enabledGrantTypes[$grantType->getIdentifier()] = $grantType;
        $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()] = $accessTokenTTL;
    }


    protected function getResponseType(Request $request, GrantTypeInterface $grantType, $encryptionKey=null)
    {
        $responseType = clone $this->responseType;

        $clientEntity = $grantType->validateClient($request);

        if(!$clientEntity instanceof ClientEntityInterface){
            throw new \Exception('Client type error');
        }

        //set up the private key
        $privateKey = $clientEntity->getPrivateKey();
        $responseType->setPrivateKey($privateKey);
        if($encryptionKey){
            $responseType->setEncryptionKey($encryptionKey);
        }else{
            $responseType->setEncryptionKey($this->encryptionKey);
        }
        return $responseType;
    }

    public function respondToAccessTokenRequest(Request $request, ResponseTypeInterface $response)
    {
        foreach ($this->enabledGrantTypes as $grantType) {
            if (!$grantType->canRespondToAccessTokenRequest($request)) {
                continue;
            }
            $tokenResponse = $grantType->respondToAccessTokenRequest(
                $request,
                $this->getResponseType($request, $grantType),
                $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()]
            );

            if ($tokenResponse instanceof ResponseTypeInterface) {
                return $tokenResponse->generateResponse($response);
            }
        }

        throw new \Exception('There is no valid token authorization server registry');
    }

    public function respondToClientRequest(Request $request)
    {
        $clientEntity = $this->clientRepository->getNewClient();
        $this->validateClientInit($request, $clientEntity);

        $this->clientRepository->persistNewClient($clientEntity);
        return $clientEntity;
    }

    protected function validateClientInit(Request $request, ClientEntityInterface $clientEntity)
    {
        $name = $request->input('client_name');
        if(empty($name)){
            throw new \Exception('Please comment on the client');
        }
        $redirect = $request->input('redirect', 'http://localhost');

        $clientEntity->setName($name);
        $clientEntity->setRedirectUri($redirect);
        $secret = $clientEntity->getSecret();
        if(empty($secret)){
            throw new \Exception('Please be the client\'s secret');
        }
        return $clientEntity;
    }
}