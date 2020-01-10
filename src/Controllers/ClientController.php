<?php

namespace Lyignore\LaravelOauth2\Http\Controllers;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Lyignore\LaravelOauth2\Api;
use Illuminate\Auth\AuthenticationException;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;
use Lyignore\LaravelOauth2\Design\Grant\CryptTrait;
use Lyignore\LaravelOauth2\Entities\ClientRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Lyignore\LaravelOauth2\Models\Client;
use Lyignore\LaravelOauth2\Models\Scope;
use phpseclib\Crypt\RSA;

class ClientController
{
    /**
     * The client repository instance.
     *
     * @var \Lyignore\LaravelOauth2\Entities\ClientRepository
     */
    protected $clients;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    /**
     * Create a client controller instance.
     *
     * @param  \Lyignore\LaravelOauth2\Entities\ClientRepository  $clients
     * @param  \Illuminate\Contracts\Validation\Factory  $validation
     * @return void
     */
    public function __construct(ClientRepository $clients,
                                ValidationFactory $validation)
    {
        $this->clients = $clients;
        $this->validation = $validation;
    }

    /**
     * Get all of the clients for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forUser(Request $request)
    {
        $userId = $request->user()->getKey();

        return $this->clients->activeForUser($userId)->makeVisible('secret');
    }

    /**
     * Store a new client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name' => 'max:255',
            'client_type' => 'in:credentials_client,password_client,authorization_client',
            'redirect' => 'url',
        ])->validate();
        $uri        = $request->input('redirect', config('app.url'));
        $grantType  = $request->input('client_type', 'credentials_client');
        $result     = $this->createdClient($uri, $grantType);
        $name       = ltrim($request->input('name', $result['client_id']));
        $oauthKey   = $this->createdKeys($name);
        $result['grant_type']= $grantType;
        return \response()->json([
            'status_code' => 200,
            'message' => 'Generated client successfully',
            'data'  => $result
        ], 200);
    }

    protected function createdKeys($identify)
    {
        $rsa = new RSA();
        $keys = $rsa->createKey(4096);
        $name = 'secret_'.$identify;
        list($publicKey, $privateKey) = [
            Api::keyPath('oauth-public.key', $name),
            Api::keyPath('oauth-private.key', $name),
        ];
        if(file_exists($publicKey) || file_exists($privateKey)){
            throw new AuthenticationException('Encryption keys already exist');
        }
        file_put_contents($publicKey, Arr::get($keys, 'publickey'));
        file_put_contents($privateKey, Arr::get($keys, 'privatekey'));
        return compact('publicKey', 'privateKey');
    }

    protected function createdClient($uri, $grantType, array $scopes=[])
    {
        $client = $this->clients->getNewClient();
        $name = $client->getIdentifier();
        $client->setName($name);
        $client->setRedirectUri($uri);
        $client->setGrantType($grantType);
        $client->setScopes(json_encode($scopes));
        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('P1Y'));
        $client->setVaildUntil($dateTime);
        $this->clients->persistNewClient($client);
        $client_id      = $client->getIdentifier();
        $client_secret  = $client->getSecret();
        $valid_at       = $client->getVaildUntil()->format('Y-m-d H:i:s');
        return compact('client_id', 'client_secret', 'valid_at');
    }

    public function bindingScope(Request $request)
    {
        $this->validation->make($request->all(), [
            'scope' => 'required|string|max:255',
        ])->validate();
        $scopes = $request->input('scope');
        $resScopes = $this->findScopes($scopes);
        $identify = $request->input('oauth_client_id');
        $bindingScope = $this->updateScopes($resScopes, $identify);
        $client = $this->clients->retrieveById($identify);
        return \response()->json([
            'status_code' => 200,
            'message' => 'Binding client successfully',
            'data'  => $client
        ], 200);
    }

    protected function findScopes($scopes)
    {
        return Scope::findScopes($scopes);
    }

    protected function updateScopes(array $scopes, $identify)
    {
        return Client::updateScopes($scopes, $identify);
    }

    /**
     * Update the given client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $clientId
     * @return \Illuminate\Http\Response|\Laravel\Passport\Client
     */
    public function update(Request $request, $clientId)
    {
//        $client = $this->clients->findForUser($clientId, $request->user()->getKey());
//
//        if (!$client) {
//            return new Response('', 404);
//        }
//
//        $this->validation->make($request->all(), [
//            'name' => 'required|max:255',
//            'redirect' => 'required|url',
//        ])->validate();
//
//        return $this->clients->update(
//            $client, $request->name, $request->redirect
//        );
    }

    /**
     * Delete the given client.
     *
     * @param  Request  $request
     * @param  string  $clientId
     * @return Response
     */
    public function destroy(Request $request, $clientId)
    {
//        $client = $this->clients->findForUser($clientId, $request->user()->getKey());
//
//        if (! $client) {
//            return new Response('', 404);
//        }
//
//        $this->clients->delete(
//            $client
//        );
    }
}
