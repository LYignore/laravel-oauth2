<?php
namespace Lyignore\LaravelOauth2\Design\ResponseTypes;

use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;

class BearerTokenResponse extends AbstractResponse
{
    public function generateResponse()
    {
        $expireDateTime = $this->accessToken->getExpiryDateTime()->getTimestamp();

        $jwtAccessToken = $this->accessToken->convertToJWT($this->privateKey);

        $responseParmas = [
            'token_type' => 'Bearer',
            'expires_in' => $expireDateTime-(new \DateTime())->getTimestamp(),
            'access_token' => $jwtAccessToken
        ];
        if($this->refreshToken instanceof RefreshTokenEntityInterface){
            $refreshToken = $this->encrypt(
                json_encode([
                    'client_id' => $this->accessToken->getClient()->getIdentifier(),
                    'refresh_token_id' => $this->refreshToken->getIdentifier(),
                    'access_token_id'  => $this->accessToken->getIdentifier(),
                    'scopes'           => $this->accessToken->getScopes(),
                    'user_id'          => $this->accessToken->getUserIdentifier()
                ])
            );
            $responseParmas['refresh_token'] = $refreshToken;
        }
        return $responseParmas;
    }
}