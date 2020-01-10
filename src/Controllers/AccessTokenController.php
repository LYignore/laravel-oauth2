<?php

namespace Lyignore\LaravelOauth2\Http\Controllers;

use Illuminate\Http\Request;
use Lcobucci\JWT\Parser as JwtParser;
use Lyignore\LaravelOauth2\Design\AuthorizationServer;
use Lyignore\LaravelOauth2\Design\ResponseTypes\BearerTokenResponse;

class AccessTokenController
{
    use HandlesOAuthErrors;

    /**
     * The authorization server.
     *
     * @var \Lyignore\LaravelOauth2\Design\AuthorizationServer
     */
    protected $server;

    /**
     * The JWT parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     */
    protected $jwt;

    /**
     * Create a new controller instance.
     *
     * @param  \Lyignore\LaravelOauth2\Design\AuthorizationServer $server
     * @param  \Lcobucci\JWT\Parser  $jwt
     * @return void
     */
    public function __construct(AuthorizationServer $server,
                                JwtParser $jwt)
    {
        $this->jwt = $jwt;
        $this->server = $server;
    }

    /**
     * Authorize a client to access the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function issueToken(Request $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            $token = $this->server->respondToAccessTokenRequest($request, new BearerTokenResponse);
            return response()->json([
                'status_code' => 200,
                'message' => 'generation token successful',
                'data'  => $token
            ], 200);
        });
    }
}
