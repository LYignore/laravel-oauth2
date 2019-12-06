<?php

namespace Lyignore\LaravelOauth2\Models;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Route;
use League\OAuth2\Server\ResourceServer;
use Mockery;

class Passport
{
    public static $implicitGrantEnabled = false;

    public static $revokeOtherTokens = false;

    public static $pruneRevokedTokens = false;

    public static $personalAccessClientId;

    public static $defaultScope;

    public static $scopes = [
        //
    ];

    public static $tokensExpireAt;

    public static $refreshTokensExpireAt;

    public static $personalAccessTokensExpireAt;

    public static $cookie = 'laravel_token';

    public static $ignoreCsrfToken = false;

    public static $keyPath;

    public static $authCodeModel = 'Laravel\Passport\AuthCode';

    public static $clientModel = 'Laravel\Passport\Client';

    public static $personalAccessClientModel = 'Laravel\Passport\PersonalAccessClient';

    public static $tokenModel = 'Laravel\Passport\Token';

    public static $refreshTokenModel = 'Laravel\Passport\RefreshToken';

    public static $runsMigrations = true;

    public static $unserializesCookies = false;

    public static $withInheritedScopes = false;

    public static function enableImplicitGrant()
    {
        static::$implicitGrantEnabled = true;

        return new static;
    }

    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }

    public static function revokeOtherTokens()
    {
        return new static;
    }

    public static function pruneRevokedTokens()
    {
        return new static;
    }

    public static function personalAccessClientId($clientId)
    {
        static::$personalAccessClientId = $clientId;

        return new static;
    }

    public static function setDefaultScope($scope)
    {
        static::$defaultScope = is_array($scope) ? implode(' ', $scope) : $scope;
    }

    public static function scopeIds()
    {
        return static::scopes()->pluck('id')->values()->all();
    }

    public static function hasScope($id)
    {
        return $id === '*' || array_key_exists($id, static::$scopes);
    }

    public static function scopes()
    {
        return collect(static::$scopes)->map(function ($description, $id) {
            return new Scope($id, $description);
        })->values();
    }

    public static function scopesFor(array $ids)
    {
        return collect($ids)->map(function ($id) {
            if (isset(static::$scopes[$id])) {
                return new Scope($id, static::$scopes[$id]);
            }
        })->filter()->values()->all();
    }

    public static function tokensCan(array $scopes)
    {
        static::$scopes = $scopes;
    }

    public static function tokensExpireIn(DateTimeInterface $date = null)
    {
        if (is_null($date)) {
            return static::$tokensExpireAt
                            ? Carbon::now()->diff(static::$tokensExpireAt)
                            : new DateInterval('P1Y');
        }

        static::$tokensExpireAt = $date;

        return new static;
    }

    public static function refreshTokensExpireIn(DateTimeInterface $date = null)
    {
        if (is_null($date)) {
            return static::$refreshTokensExpireAt
                            ? Carbon::now()->diff(static::$refreshTokensExpireAt)
                            : new DateInterval('P1Y');
        }

        static::$refreshTokensExpireAt = $date;

        return new static;
    }

    public static function personalAccessTokensExpireIn(DateTimeInterface $date = null)
    {
        if (is_null($date)) {
            return static::$personalAccessTokensExpireAt
                ? Carbon::now()->diff(static::$personalAccessTokensExpireAt)
                : new DateInterval('P1Y');
        }

        static::$personalAccessTokensExpireAt = $date;

        return new static;
    }

    public static function cookie($cookie = null)
    {
        if (is_null($cookie)) {
            return static::$cookie;
        }

        static::$cookie = $cookie;

        return new static;
    }

    public static function ignoreCsrfToken($ignoreCsrfToken = true)
    {
        static::$ignoreCsrfToken = $ignoreCsrfToken;

        return new static;
    }

    public static function actingAs($user, $scopes = [], $guard = 'api')
    {
        $token = Mockery::mock(self::tokenModel())->shouldIgnoreMissing(false);

        foreach ($scopes as $scope) {
            $token->shouldReceive('can')->with($scope)->andReturn(true);
        }

        $user->withAccessToken($token);

        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        app('auth')->guard($guard)->setUser($user);

        app('auth')->shouldUse($guard);

        return $user;
    }

    public static function actingAsClient($client, $scopes = [])
    {
        $token = app(self::tokenModel());
        $token->client = $client;
        $token->scopes = $scopes;

        $mock = Mockery::mock(ResourceServer::class);
        $mock->shouldReceive('validateAuthenticatedRequest')
            ->andReturnUsing(function ($request) use ($token) {
                return $request->withAttribute('oauth_client_id', $token->client->id)
                    ->withAttribute('oauth_access_token_id', $token->id)
                    ->withAttribute('oauth_scopes', $token->scopes);
            });

        app()->instance(ResourceServer::class, $mock);

        $mock = Mockery::mock(TokenRepository::class);
        $mock->shouldReceive('find')->andReturn($token);

        app()->instance(TokenRepository::class, $mock);

        return $client;
    }

    public static function loadKeysFrom($path)
    {
        static::$keyPath = $path;
    }

    public static function keyPath($file)
    {
        $file = ltrim($file, '/\\');

        return static::$keyPath
            ? rtrim(static::$keyPath, '/\\').DIRECTORY_SEPARATOR.$file
            : storage_path($file);
    }


    public static function useAuthCodeModel($authCodeModel)
    {
        static::$authCodeModel = $authCodeModel;
    }


    public static function authCodeModel()
    {
        return static::$authCodeModel;
    }


    public static function authCode()
    {
        return new static::$authCodeModel;
    }


    public static function useClientModel($clientModel)
    {
        static::$clientModel = $clientModel;
    }

    public static function clientModel()
    {
        return static::$clientModel;
    }

    public static function client()
    {
        return new static::$clientModel;
    }


    public static function usePersonalAccessClientModel($clientModel)
    {
        static::$personalAccessClientModel = $clientModel;
    }

    public static function personalAccessClientModel()
    {
        return static::$personalAccessClientModel;
    }

    public static function personalAccessClient()
    {
        return new static::$personalAccessClientModel;
    }

    public static function useTokenModel($tokenModel)
    {
        static::$tokenModel = $tokenModel;
    }

    public static function tokenModel()
    {
        return static::$tokenModel;
    }

    public static function token()
    {
        return new static::$tokenModel;
    }

    public static function useRefreshTokenModel($refreshTokenModel)
    {
        static::$refreshTokenModel = $refreshTokenModel;
    }

    public static function refreshTokenModel()
    {
        return static::$refreshTokenModel;
    }

    public static function refreshToken()
    {
        return new static::$refreshTokenModel;
    }

    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    public static function withCookieSerialization()
    {
        static::$unserializesCookies = true;

        return new static;
    }

    public static function withoutCookieSerialization()
    {
        static::$unserializesCookies = false;

        return new static;
    }
}
