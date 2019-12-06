<?php
namespace Lyignore\LaravelOauth2\Design;

use Illuminate\Http\Request;
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
        $privateKey,
        $encryptionKey,
        ResponseTypeInterface $responseType=null
    ) {
        $this->clientRepository = $clientRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->scopeRepository = $scopeRepository;

        if ($privateKey instanceof CryptKey === false) {
            $privateKey = new CryptKey($privateKey);
        }

        $this->privateKey = $privateKey;
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
        $grantType->setPrivateKey($this->privateKey);
        $grantType->setEncryptionKey($this->encryptionKey);

        $this->enabledGrantTypes[$grantType->getIdentifier()] = $grantType;
        $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()] = $accessTokenTTL;
    }


    protected function getResponseType()
    {
        $responseType = clone $this->responseType;

        $this->enabledGrantTypes($responseType);

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
                $this->getResponseType(),
                $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()]
            );

            if ($tokenResponse instanceof ResponseTypeInterface) {
                return $tokenResponse->generateResponse($response);
            }

        }

        throw new \Exception();
    }
}