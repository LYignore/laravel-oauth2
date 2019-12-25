<?php
namespace Lyignore\LaravelOauth2\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Lyignore\LaravelOauth2\Design\AuthenticationServer;
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
            throw new \Exception('without token');
        }
    }

    public function authenticateViaBearerToken(Request $request)
    {
        $resquestWithToken = $this->server->validateAuthenticated($request);
        $user = $this->provider->retrieveById(
            //$resquestWithToken['oauth_client_id']
            $resquestWithToken->input('oauth_user_id')
        );
        if (!$user) {
            return;
        }

        $token = $this->tokens->find(
            //$resquestWithToken['oauth_access_token_id']
            $request->input('oauth_access_token_id')
        );
        $clientId = $resquestWithToken->input('oauth_client_id');

        if ($this->clients->revoked($clientId)) {
            return;
        }
        return $token ? $user->withAccessToken($token) : null;
    }
}
