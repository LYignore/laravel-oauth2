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

    private $accessTokenRepository;

    protected $publicKey;

    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function setPublicKey(CryptKey $key)
    {
        $this->publicKey = $key;
    }

    public function validateAuthorization(Request $request)
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

            // Check if token has been revoked
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw new \Exception('Access token has been revoked');
            }
            return [
                'oauth_access_token_id' => $token->getClaim('jti'),
                'oauth_client_id' => $token->getClaim('aud'),
                'oauth_user_id' => $token->getClaim('sub'),
                'oauth_scopes'  => $token->getClaim('scopes')
            ];
        } catch (\Exception $exception) {
            // JWT couldn't be parsed so return the request as is
            throw new \Exception($exception->getMessage());
        }
    }
}