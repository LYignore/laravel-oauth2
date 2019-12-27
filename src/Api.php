<?php
namespace Lyignore\LaravelOauth2;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class Api
{
    public static $authorizationCodeGrantEnable = false;
    /**
     * Oauth2 password Grant Type
     * @var bool|true
     */
    public static $passwordGrantEnabled = true;
    /**
     * Oauth2 implicit Grant Type
     * @var bool|true
     */
    public static $implicitGrantEnabled = false;

    /**
     * Oauth2 client credentials Grant Type
     * @var bool|true
     */
    public static $clientCredentialsGrantEnabled = true;


    public static $refreshTokenGrantEnabled = false;


    public static $scopes = [];

    public static $authCodeExpireAt;

    public static $tokenExpireAt;

    public static $refreshTokenExpireAt;

    public static $cookie = 'oauth_token';

    public static $keyPath;

    public static $runsMigrations = true;

    public static function enableImplicitGrant()
    {
        static::$implicitGrantEnabled = true;

        return new static;
    }

    public static function enableClientCredentialsGrant()
    {
        static::$clientCredentialsGrantEnabled = true;

        return new static;
    }

    /**
     * Register the routes involved in the token service
     * @params Closure $callback
     * @params array $options
     * @return void
     */
    public static function registerRoutes($callback = null, array $options = [])
    {
        $callback = $callback?:function($router){
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Lyignore\LaravelOauth2\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function($router) use($callback){
            $callback(new RouteRegistrar($router));
        });
    }

    /**
     * See all scopes of the service
     */
    public static function scopes()
    {
        return collect(static::$scopes)->map(function($description, $id){

        })->values();
    }

    public static function scopeIds()
    {
        return static::scopes()->pluck('id')->values()->all();
    }

    /**
     * See if scopes exist
     * @params string $id
     * return boole
     */
    public static function hasScope($id)
    {
        return $id === '*'||array_key_exists($id, static::$scopes);
    }

    /**
     * Set up valid scopes for authorized services
     * @params array $scopes
     */
    public static function scopesFor(array $ids)
    {
        return collect($ids)->map(function($id){
            if(isset(static::$scopes[$id])){
                return new self();
            }
            return;
        })->filter()->values()->all();
    }

    /**
     *  Scopes setting up services
     *  @params array $scopes
     *  @return void
     */
    public static function tokensCan(array $scopes)
    {
        static::$scopes = $scopes;
    }

    public static function authCodeExpireIn($date=null)
    {
        static::$authCodeExpireAt = static::$authCodeExpireAt
            ? Carbon::now()->diff(static::$authCodeExpireAt)
            : new \DateInterval('PT1M');
        return static::$authCodeExpireAt;
    }

    /**
     * Set the token expiration time
     * @param obj \DateTimeInterface
     * return static
     */
    public static function tokenExpireIn(\DateTimeInterface $date = null)
    {
        if(is_null($date)){
            return static::$tokenExpireAt
                        ? Carbon::now()->diff(static::$tokenExpireAt)
                        : new \DateInterval('P1Y');
        }
        static::$tokenExpireAt = $date;
        return new static;
    }

    /**
     * Set the refreshToken expiration time
     * @param obj \DateTimeInterface
     * return static
     */
    public static function refreshTokenExpireIn(\DateTimeInterface $date = null)
    {
        if(is_null($date)){
            return static::$refreshTokenExpireAt
                ? Carbon::now()->diff(static::$refreshTokenExpireAt)
                : new \DateInterval('P1Y');
        }
        static::$refreshTokenExpireAt = $date;
        return static::$refreshTokenExpireAt;
    }

    /**
     * Get or set the name for API token cookies.
     *
     * @param  string|null  $cookie
     * @return static
     */
    public static function cookie($cookie = null)
    {
        if (is_null($cookie)) {
            return static::$cookie;
        }

        static::$cookie = $cookie;

        return new static;
    }

    /**
     * Set the current user for the application with the given scopes.
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $scopes
     * @param  string  $guard
     * @return void
     */
    public static function actiongAs($user, $scopes = [], $guard = 'api')
    {
        app('auth')->guard($guard)->setUser($user);

        app('auth')->shouldUse($guard);
    }

    /**
     * Set public key name
     * @params string $keyPath
     * @return void
     */
    public static function loadKeysFrom($keyPath)
    {
        static::$keyPath = $keyPath;
    }

    /**
     * Sets the absolute address of the public key
     * @params string $file
     * @params string $secondLevel
     * @return string
     */
    public static function keyPath($file, $secondLevel=null, $defaultFolder = 'secret')
    {
        $file = ltrim($file, '/\\');
        if(is_null($secondLevel)){
            return static::$keyPath
                ? rtrim(static::$keyPath, '/\\').DIRECTORY_SEPARATOR.$file
                : storage_path($file);
        }else{
            $secondLevel = ltrim($secondLevel, '/\\');
            $path = static::$keyPath?rtrim(static::$keyPath, '/\\').DIRECTORY_SEPARATOR.$defaultFolder.DIRECTORY_SEPARATOR.$secondLevel:
                storage_path().DIRECTORY_SEPARATOR.$defaultFolder.DIRECTORY_SEPARATOR.$secondLevel;
            if(!is_dir($path)){
                $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true);
                if(!$res){
                    throw new \Exception('Sorry, no permission to create a directory');
                }
            }
            $filePath = $path.DIRECTORY_SEPARATOR.$file;
            if(is_file($filePath)){
                throw new \Exception('Sorry, the file "'.$path.'" already exists');
            }
            return $filePath;
        }
    }
}
