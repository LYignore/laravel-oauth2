<?php
namespace Lyignore\LaravelOauth2\Guards;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\AuthenticationServer;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;
use Lyignore\LaravelOauth2\Design\ResponseTypes\ResponseTypeInterface;
use Lyignore\LaravelOauth2\Entities\AccessTokenRepository;
use Lyignore\LaravelOauth2\Entities\ClientRepository;

class TokenGuard
{
    protected $server;

    protected $provider;

    protected $tokens;

    protected $client;

    protected $encrypter;

    public function __construct(
        AuthenticationServer $server,
        UserProvider $provider,
        AccessTokenRepository $tokens,
        ClientRepository $clients,
        Encrypter $encrypter)
    {
        $this->server = $server;
        $this->provider = $provider;
        $this->tokens = $tokens;
        $this->clients = $clients;
        $this->encrypter = $encrypter;
    }

    public function user(Request $request)
    {
        if ($request->bearerToken()) {
            return $this->authenticateViaBearerToken($request);
        }else{
            throw new AuthenticationException('without token');
        }
    }

    public function authenticateViaBearerToken(Request $request)
    {
        $resquestWithToken = $this->server->validateAuthenticated($request, true);
        $grantType = $resquestWithToken->input('grant_type');
        $userId = $resquestWithToken->input('oauth_user_id');
        $clientId = $resquestWithToken->input('oauth_client_id');
        if($grantType == ClientCredentialsGrant::IDENTIFIER){
            // 客户端模式
            $user = $this->clients->retrieveById($clientId);
        }else{
            $user = $this->provider->retrieveById($userId);
        }
        if (!$user) {
            return;
        }
//        $token = $this->tokens->find(
//            $resquestWithToken->input('oauth_access_token_id')
//        );
        if ($this->clients->isClientRevoked($clientId)) {
            return;
        }
        return $user ?: null;
    }
}
