<?php

namespace Lyignore\LaravelOauth2\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;
use Laravel\Passport\Passport;
use Lyignore\LaravelOauth2\Entities\ScopeRepository;
use Lyignore\LaravelOauth2\Models\Scope;

class ScopeController
{
    protected $scopes;

    protected $validation;

    public function __construct(ScopeRepository $scopeRepository,
                                ValidationFactory $validation)
    {
        $this->scopes = $scopeRepository;
        $this->validation = $validation;
    }

    /**
     * Get all of the available scopes for the application.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return Passport::scopes();
    }

    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name'  => 'required|string|max:255',
            'uri'   => 'url',
            'detail'=> 'string'
        ])->validate();
        try{
            $name = $request->input('name');
            $scope = $this->scopes->getNewScope($name);
            if($request->exists('uri')){
                $uri = $request->input('uri');
                $scope->setUri($uri);
            }
            if($request->exists('detail')){
                $detail = $request->input('detail');
                $scope->setDescription($detail);
            }
            $this->scopes->persistNewScope($scope);
            $scopesModel = Scope::where('id', $scope->getIdentifier())->where('revoked', false)->first();
            return response()->json([
                'status_code' => 200,
                'message' => 'Generated scope successfully',
                'data'  => $scopesModel
            ], 200);
        }catch (\Exception $e){
            throw new AuthenticationException($e->getMessage());
        }
    }

    public function find(Request $request)
    {
        $this->validation->make($request->all(), [
            'name'  => 'required|string|max:255',
        ])->validate();
        try{
            $name = $request->input('name');
            $scope = $this->scopes->getScopeEntityByIdentifier($name, true);
            return response()->json([
                'status_code' => 200,
                'message' => 'The query scope successfully',
                'data'  => $scope
            ], 200);
        }catch (\Exception $e){
            throw new AuthorizationException($e->getMessage());
        }
    }
}
