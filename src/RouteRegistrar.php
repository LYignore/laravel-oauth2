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
    }

    public function forAuthorization()
    {
        $this->router->group(['middleware' => ['web', 'auth'], function($router){
            $router->get('/authorize', [
                'uses' => 'AuthorizationController@authorize',
            ]);

            $router->post('/authorize', [
                'uses' => 'ApproveAuthorizationController@approve',
            ]);

            $router->delete('/authorize', [
                'uses' => 'DenyAuthorizationController@deny',
            ]);
        }]);
    }

    public function forAccessTokens()
    {
        $this->router->group('/token', [
            'uses' => 'AccessTokenController@issueToken',
            'middleware' => 'throttle'
        ]);

        $this->router->group(['middleware' => ['web', 'auth']], function($router){
            $router->get('/tokens',[
                'uses' => 'AuthorizedAccessTokenController@forUser',
            ]);
            $router->delete('/token/{token_id}', [
                'uses' => 'AuthorizedAccessTokenController@destory',
            ]);
        });
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
        $this->router->group(['middleware' => ['web', 'auth']], function ($router){
            $router->get('clients', [
                'uses' => 'ClientController@forUser',
            ]);

            $router->post('/clients', [
                'uses' => 'ClientController@store',
            ]);

            $router->post('/clients/{$client_id}',[
                'uses' => 'ClientController@update',
            ]);

            $router->delete('/clients/{$client_id}', [
                'uses' => 'ClientController@destroy',
            ]);
        });
    }
}