<?php
namespace Lyignore\LaravelOauth2;

use Illuminate\Contracts\Routing\Registrar;
class RouteRegistrar
{
    protected $router;

    public function __construct(Registrar $router)
    {
        $this->router = $router;
    }

    public function all()
    {
        $this->forAuthorization();
        $this->forAccessTokens();
        $this->forTransientTokens();
        $this->forClients();
        $this->forScopes();
    }

    public function forAuthorization()
    {
//        $this->router->group(['middleware' => ['web', 'auth']], function($router){
//            $router->get('/authorize', [
//                'uses' => 'AuthorizationController@authorize',
//            ]);
//
//            $router->post('/authorize', [
//                'uses' => 'ApproveAuthorizationController@approve',
//            ]);
//
//            $router->delete('/authorize', [
//                'uses' => 'DenyAuthorizationController@deny',
//            ]);
//        });
    }

    public function forAccessTokens()
    {
        $this->router->post('/token', [
            'uses' => 'AccessTokenController@issueToken',
            'middleware' => 'throttle'
        ]);

//        $this->router->group(['middleware' => ['web', 'auth']], function($router){
//            $router->get('/tokens',[
//                'uses' => 'AuthorizedAccessTokenController@forUser',
//            ]);
//            $router->delete('/token/{token_id}', [
//                'uses' => 'AuthorizedAccessTokenController@destory',
//            ]);
//        });
    }

    public function forTransientTokens()
    {
        $this->router->post('/token/refresh', [
            'middleware' => ['web', 'auth'],
            'uses' => 'TransientTokenController@refresh',
        ]);
    }

    public function forClients()
    {
        $this->router->group(['middleware' => ['auth:oauth']], function ($router){
            $router->post('/clients', [
                'uses' => 'ClientController@store',
            ]);

//            $router->post('/clients/{$client_id}',[
//                'uses' => 'ClientController@update',
//            ]);
//
//            $router->delete('/clients/{$client_id}', [
//                'uses' => 'ClientController@destroy',
//            ]);

            $router->post('/clients/scope', [
                'uses' => 'ClientController@bindingScope',
            ]);
        });
    }

    public function forScopes()
    {
        $this->router->group(['middleware' => ['auth:oauth']], function ($router){
            $router->post('/scopes', [
                'uses' => 'ScopeController@store',
            ]);

            $router->post('/scopes/find',[
                'uses' => 'ScopeController@find',
            ]);
        });
    }
}