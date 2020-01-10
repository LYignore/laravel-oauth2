<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\AccessTokenEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;
use Lyignore\LaravelOauth2\Design\Entities\UserRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\AuthCodeRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ClientRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Repositories\ScopeRepositoryInterface;

abstract class AbstractGrant implements GrantTypeInterface
{
    use CryptTrait;

    const MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS = 10;
    const SCOPE_DELIMITER_STRING = ',';

    protected $privateKey;

    protected $publicKey;

    protected $refreshTokenTTL;

    protected $refreshTokenRepository;

    protected $scopeRepository;

    protected $authCodeRepository;

    protected $userRepository;

    protected $clientRepository;

    protected $accessTokenRepository;

    public function setRefreshTokenTTL(\DateInterval $refreshTokenTTL)
    {
        $this->refreshTokenTTL = $refreshTokenTTL;
    }

    public function setClientRepository(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
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

    public function setUserRepository(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function setScopeRepository(ScopeRepositoryInterface $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    public function setAccessTokenRepository(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function setAuthCodeRepository(AuthCodeRepositoryInterface $authCodeRepository)
    {
        $this->authCodeRepository = $authCodeRepository;
    }

    public function setPrivateKey(CryptKey $cryptKey)
    {
        $this->privateKey = $cryptKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPublicKey(CryptKey $cryptKey)
    {
        $this->publicKey = $cryptKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function canRespondToAccessTokenRequest(Request $request)
    {
        $params = (array)$request->all();
        return array_key_exists('grant_type', $params) &&
            $params['grant_type'] == $this->getIdentifier();
    }

    public function issueAccessToken($clientIdentifier, UserEntityInterface $userEntity, \DateInterval $dateInterval, array $scopes = [])
    {
        $maxGenerateAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;
        while($maxGenerateAttempts-->0){
            try{
                $clientEntity = $this->clientRepository->getClientEntity($clientIdentifier, $this->getIdentifier());
                $accessToken = $this->accessTokenRepository->getNewAccessToken($clientEntity, $scopes, $userEntity);
                $accessToken->setIdentifier($this->generateUniqueIdentifier());
                $dateTime = new \DateTime();
                $dateTime->add($dateInterval);
                $accessToken->setExpiryDateTime($dateTime);
                $this->accessTokenRepository->persistNewAccessToken($accessToken);
                return $accessToken;
            }catch (\Exception $e){
                if($maxGenerateAttempts<=0){
                    throw new \Exception($e);
                }
            }
        }
    }

    public function issueRefreshToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $maxGenerateAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;
        while($maxGenerateAttempts-->0){
            try{
                $refreshToken = $this->refreshTokenRepository->getNewRefreshToken();
                $dateTime = new \DateTime();
                $dateTime->add($this->refreshTokenTTL);
                $refreshToken->setExpiryDateTime($dateTime);
                $this->refreshTokenRepository->persistRefreshToken($refreshToken);
                return $refreshToken;
            }catch (\Exception $e){
                if($maxGenerateAttempts<=0){
                    throw new \Exception($e);
                }
            }
        }
    }

    public function defaultScope()
    {
        return '*';
    }

    protected function generateUniqueIdentifier($length = 40)
    {
        try{
            return bin2hex(random_bytes($length));
        }catch (\Exception $e){
            throw new \Exception($e);
        }
    }
}