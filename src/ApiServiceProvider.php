<?php
namespace Lyignore\LaravelOauth2;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->registerAuthorizationServer();
    }

    /**
     * Registration authorization server
     * @return void
     */
    protected function registerAuthorizationServer()
    {
        $this->app->singleton(AuthorizationServer::class, function(){
            return tap($this->makeAuthorizationServer(), function($server){
                if(Api::$authorizationCodeGrantEnable){
                    $server->enableGrantType(
                        $this->buildAuthCodeGrant(), Api::tokenExpireIn()
                    );
                }

                if(Api::$passwordGrantEnabled){
                    $server->enableGrantType(
                        $this->buildPasswordGrant(), Api::tokenExpireIn()
                    );
                }

                if(Api::$implicitGrantEnabled){
                    $server->enableGrantType(
                        $this->buildImplicitGrant(), Api::tokenExpireIn()
                    );
                }

                if(Api::$clientCredentialsGrantEnabled){
                    $server->enableGrantType(
                        new ClientCredentialsGrant(), Api::tokenExpireIn()
                    );
                }

                if(Api::$refreshTokenGrantEnabled){
                    $server->enableGrantType(
                        $this->buildRefreshTokenGrant(), Api::tokenExpireIn()
                    );
                }
            });
        });
    }


    /**
     * Register the resource server
     * @return void
     */
    protected function registerResourceServer()
    {
        $this->app->singleton(ResourceServer::class, function(){
            return new ResourceServer(
                $this->app->make(),
                $this->makeCryptKey('oauth-public.key')
            );
        });
    }


    /**
     * Register the token guard.
     * @return void
     */
    protected function registerGuard()
    {
        Auth::extend('oauth', function ($app, $name, array $config){
            return tap($this->makeGuard($config), function($guard){
                $this->app->refresh('request', $guard, 'setRequest');
            });
        });
    }


    /**
     * Build and configure an instance of the Implicit grant.
     *
     * @return \League\OAuth2\Server\Grant\ImplicitGrant
     */
    protected function buildImplicitGrant()
    {
        return new ImplicitGrant(Api::tokenExpireIn());
    }

    /**
     * Build and configure a Password grant instance.
     *
     * @return \League\OAuth2\Server\Grant\PasswordGrant
     */
    protected function buildPasswordGrant()
    {
        $grant = new PasswordGrant();

        $grant->setRefreshTokenTTL(Api::refreshTokenExpireIn());
        return $grant;
    }

    /**
     * Build the Auth Code grant instance.
     *
     * @return \League\OAuth2\Server\Grant\AuthCodeGrant
     */
    protected function buildAuthCodeGrant()
    {
        $grant = new AuthCodeGrant();
        $grant->setRefreshTokenTTL(Api::refreshTokenExpireIn());
        return $grant;
    }

    /**
     * Build and configure a Refresh Token grant instance.
     *
     * @return \League\OAuth2\Server\Grant\RefreshTokenGrant
     */
    protected function buildRefreshTokenGrant()
    {
        $grant = new RefreshTokenGrant();
        $grant->setRefreshTokenTTL(Api::refreshTokenExpireIn());
        return $grant;
    }


    /**
     *  Create a CryptKey instance without permissions check
     * @param string $key
     * @return \League\OAuth2\Server\CryptKey
     */
    protected function makeCryptKey($key)
    {
        return new CryptKey(
            'file://'.Api::keyPath($key),
            null,
            false
        );
    }

    /**
     * Make an instance of the token guard
     * @param array $config
     * @return \Illuminate\\Auth\RequestGuard
     */
    protected function makeGuard($config)
    {
        return new RequestGuard(function($request) use($config){
            return (new TokenGard(
                $this->app->make(ResourceServer::class),
                Auth::createUserProvider($config['provider']),
                $this->app->make(BearerTokenResponse::class),
                $this->app->make(ClientRepositoryInterface::class),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }
}