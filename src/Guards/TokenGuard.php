<?php
namespace Lyignore\LaravelOauth2\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Encryption\Encrypter;
use League\OAuth2\Server\ResourceServer;

class TokenGuard
{
    protected $server;

    protected $provider;

    protected $tokens;

    protected $client;

    protected $encrypter;

    public function __construct(
        ResourceServer $server,
        UserProvider $provider,
        TokenRepository $tokens,
        ClientRepository $clients,
        Encrypter $encrypter)
    {
        $this->server = $server;
        $this->provider = $provider;
        $this->tokens = $tokens;
        $this->clients = $clients;
        $this->encrypter = $encrypter;
    }

    public function user()
    {

    }

}