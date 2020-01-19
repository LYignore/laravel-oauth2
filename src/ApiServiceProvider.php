<?php
namespace Lyignore\LaravelOauth2;

use Illuminate\Auth\RequestGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Lyignore\LaravelOauth2\Design\AuthenticationServer;
use Lyignore\LaravelOauth2\Design\AuthorizationServer;
use Lyignore\LaravelOauth2\Design\CryptKey;
use Lyignore\LaravelOauth2\Design\Grant\AuthCodeGrant;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;
use Lyignore\LaravelOauth2\Design\Grant\PasswordGrant;
use Lyignore\LaravelOauth2\Design\Grant\RefreshTokenGrant;
use Lyignore\LaravelOauth2\Design\ResponseTypes\BearerTokenResponse;
use Lyignore\LaravelOauth2\Entities\AccessTokenRepository;
use Lyignore\LaravelOauth2\Entities\AuthCodeRepository;
use Lyignore\LaravelOauth2\Entities\ClientRepository;
use Lyignore\LaravelOauth2\Entities\RefreshTokenRepository;
use Lyignore\LaravelOauth2\Entities\ScopeRepository;
use Lyignore\LaravelOauth2\Entities\UserRepository;
use Lyignore\LaravelOauth2\Guards\TokenGuard;
use Lyignore\LaravelOauth2\Models\Client;

class ApiServiceProvider extends ServiceProvider
{
    public static $runsMigrations = true;
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'passport');

        //$this->deleteCookieOnLogout();

        if($this->app->runningInConsole()){
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/passport'),
            ], 'passport-views');

            $this->publishes([
                __DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/passport'),
            ], 'passport-components');

            $this->commands([
                Console\InstallCommand::class,
                Console\ClientCommand::class,
                Console\KeysCommand::class,
            ]);
        }
    }

    protected function registerMigrations()
    {
        if (self::$runsMigrations) {
            return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'passport-migrations');
    }

    public function register()
    {
        $this->registerAuthorizationServer();

        $this->registerAuthenticationServer();

        $this->registerGuard();
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
                    $credentialsTime = new \DateInterval('PT7000S');
                    $server->enableGrantType(
                        new ClientCredentialsGrant(), Api::tokenExpireIn($credentialsTime)
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

    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            $this->app->make(ClientRepository::class),
            $this->app->make(AccessTokenRepository::class),
            $this->app->make(ScopeRepository::class),
            app('encrypter')->getKey()
        );
    }


    /**
     * Register the resource server
     * @return void
     */
    protected function registerAuthenticationServer()
    {
        $this->app->singleton(AuthenticationServer::class, function(){
            return new AuthenticationServer(
                $this->app->make(AccessTokenRepository::class),
                $this->app->make(ClientRepository::class),
                true
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

    protected function buildImplicitGrant()
    {
        return false;
        //return new ImplicitGrant(Api::tokenExpireIn());
    }

    /**
     * Build and configure a Password grant instance.
     *
     * @return \Lyignore\LaravelOauth2\Design\Grant\PasswordGrant
     */
    protected function buildPasswordGrant()
    {
        $grant = new PasswordGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class),
            Api::refreshTokenExpireIn()
        );
        return $grant;
    }

    /**
     * Build the Auth Code grant instance.
     *
     * @return \Lyignore\LaravelOauth2\Design\Grant\AuthCodeGrant
     */
    protected function buildAuthCodeGrant()
    {
        $grant = new AuthCodeGrant(
            $this->app->make(AuthCodeRepository::class),
            $this->app->make(ScopeRepository::class),
            app('encrypter')->getKey(),
            Api::authCodeExpireIn()
        );
        $grant->setRefreshTokenTTL(Api::refreshTokenExpireIn());
        return $grant;
    }

    /**
     * Build and configure a Refresh Token grant instance.
     *
     * @return \Lyignore\LaravelOauth2\Design\Grant\RefreshTokenGrant
     */
    protected function buildRefreshTokenGrant()
    {
        $grant = new RefreshTokenGrant(
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Api::refreshTokenExpireIn());
        return $grant;
    }


    /**
     *  Create a CryptKey instance without permissions check
     * @param string $key
     * @return \Lyignore\LaravelOauth2\Design\CryptKey
     */
    protected function makeCryptKey($key, $secondLevel = null)
    {
        return new CryptKey(
            'file://'.Api::keyPath($key, $secondLevel),
            null,
            false
        );
    }

    /**
     * Make an instance of the token guard
     * @param array $config
     * @return \Illuminate\Auth\RequestGuard
     */
    protected function makeGuard($config)
    {
        return new RequestGuard(function($request) use($config){
            return (new TokenGuard(
                $this->app->make(AuthenticationServer::class),
                Auth::createUserProvider($config['provider']),
                $this->app->make(AccessTokenRepository::class),
                $this->app->make(ClientRepository::class),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }
}
