<?php
namespace Lyignore\LaravelOauth2\Design;
use http\Exception\BadMethodCallException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use Lyignore\LaravelOauth2\Design\Exceptions\AuthenticationException as AuthException;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;
use Lyignore\LaravelOauth2\Entities\ClientRepository;

class AuthenticationServer
{
    use CryptTrait;

    protected $accessTokenRepository;

    protected $clientRepository;

    protected $publicKey;

    protected $response;

    /**
     * Generate authentication server
     *
     * @param \Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface $accessTokenRepository
     * @param \Lyignore\LaravelOauth2\Entities\ClientRepository $clientRepository
     * @param bool $response
     * @return void
     */
    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        ClientRepository $clientRepository,
        $response=false
    ){
        $this->accessTokenRepository = $accessTokenRepository;
        $this->clientRepository = $clientRepository;
        $this->response = $response;
    }

    public function setPublicKey(CryptKey $key)
    {
        $this->publicKey = $key;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Verify whether the token of common request is valid, judge the basic
     *  information such as time, and return the carrier information effectively
     *
     * @param \Illuminate\Http\Request $request
     * @param bool $response
     * @return \Illuminate\Http\Request|array
     */
    public function validateAuthenticated(Request $request,$response = false)
    {
        if ($request->hasHeader('authorization') === false) {
            throw new AuthException('Missing "Authorization" header');
        }

        $bearerToken = $request->header('authorization');
        $jwt = trim((string) preg_replace('/^(?:\s+)?Bearer\s/', '', $bearerToken));

        try {
            $token = (new Parser())->parse($jwt);
            $clientResitory = $this->clientRepository->getClientEntity($token->getClaim('aud'), $token->getClaim('grant_type'));
            $this->setPublicKey($clientResitory->getPublicKey());
            if ($token->verify(new Sha256(), $this->publicKey->getKeyPath()) === false) {
                throw new AuthException('Access token could not be verified');
            }
            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if ($token->validate($data) === false) {
                throw new AuthException('Access token is invalid');
            }

            // Determine if the access token is invalid
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw new AuthException('Access token has been revoked');
            }

            if($response){
                $request->offsetSet('oauth_access_token_id', $token->getClaim('jti'));
                $request->offsetSet('oauth_client_id', $token->getClaim('aud'));
                $request->offsetSet('oauth_user_id', $token->getClaim('sub'));
                $request->offsetSet('oauth_scopes', $token->getClaim('scopes'));
                $request->offsetSet('grant_type', $token->getClaim('grant_type'));
                return $request;
            }else{
                $result = [
                    'oauth_access_token_id' => $token->getClaim('jti'),
                    'oauth_client_id' => $token->getClaim('aud'),
                    'oauth_user_id' => $token->getClaim('sub'),
                    'oauth_scopes'  => $token->getClaim('scopes'),
                    'grant_type'    => $token->getClaim('grant_type')
                ];
                return $result;
            }
        } catch (AuthException $e) {
            // JWT couldn't be parsed so return the request as is
            throw new AuthenticationException($e->getMessage());
        } catch (\Exception $e) {
            throw new AuthenticationException('Access token is not signed');
        }
    }

}
