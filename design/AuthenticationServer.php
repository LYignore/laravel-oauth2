<?php
namespace Lyignore\LaravelOauth2\Design;
use http\Exception\BadMethodCallException;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;
use Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface;

class AuthenticationServer
{
    use CryptTrait;

    protected $accessTokenRepository;

    protected $publicKey;

    protected $response;

    /**
     * Generate authentication server
     *
     * @param \Lyignore\LaravelOauth2\Design\Repositories\AccessTokenRepositoryInterface $accessTokenRepository
     * @param \Lyignore\LaravelOauth2\Design\Grant\CryptTrait|string $publicKey
     * @return void
     */
    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $publicKey,
        $response=false
    ){
        $this->accessTokenRepository = $accessTokenRepository;
        if ($publicKey instanceof CryptKey === false) {
            $publicKey = new CryptKey($publicKey);
        }
        $this->response = $response;
        $this->publicKey = $publicKey;
    }

    public function setPublicKey(CryptKey $key)
    {
        $this->publicKey = $key;
    }

    /**
     * Verify whether the token of common request is valid, judge the basic
     *  information such as time, and return the carrier information effectively
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Request|array
     */
    public function validateAuthenticated(Request $request)
    {
        if ($request->hasHeader('authorization') === false) {
            throw new \Exception('Missing "Authorization" header');
        }

        $header = $request->input('authorization');
        $jwt = trim((string) preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));

        try {
            $token = (new Parser())->parse($jwt);
            try {
                if ($token->verify(new Sha256(), $this->publicKey->getKeyPath()) === false) {
                    throw new \Exception('Access token could not be verified');
                }
            } catch (BadMethodCallException $exception) {
                throw new \Exception('Access token is not signed');
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if ($token->validate($data) === false) {
                throw new \Exception('Access token is invalid');
            }

            // Determine if the access token is invalid
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw new \Exception('Access token has been revoked');
            }
            $result = [
                'oauth_access_token_id' => $token->getClaim('jti'),
                'oauth_client_id' => $token->getClaim('aud'),
                'oauth_user_id' => $token->getClaim('sub'),
                'oauth_scopes'  => $token->getClaim('scopes')
            ];
            if($this->response){
                $request->offsetSet('oauth_access_token_id', $token->getClaim('jti'));
                $request->offsetSet('oauth_client_id', $token->getClaim('aud'));
                $request->offsetSet('oauth_user_id', $token->getClaim('sub'));
                $request->offsetSet('oauth_scopes', $token->getClaim('scopes'));
                return $request;
            }else{
                return $result;
            }
        } catch (\Exception $exception) {
            // JWT couldn't be parsed so return the request as is
            throw new \Exception($exception->getMessage());
        }
    }

}
