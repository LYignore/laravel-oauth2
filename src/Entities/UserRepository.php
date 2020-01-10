<?php
namespace Lyignore\LaravelOauth2\Entities;

use http\Exception\RuntimeException;
use Lyignore\LaravelOauth2\Design\Entities\UserRepositoryInterface;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;

class UserRepository implements UserRepositoryInterface
{
    protected $user;

    protected $userModel;

    public function getUserEntityByUserCrentials($username, $password, $grantType)
    {
        if($grantType == ClientCredentialsGrant::IDENTIFIER){
            return new User($username);
        }
        $provider = config('auth.guards.api.provider');

        if(is_null($model = config('auth.providers.'. $provider. '.model'))){
            throw new RuntimeException('Unable to determine authentication model from configuration');
        }

        if(method_exists($model, 'FindForPassport')){
            $user = (new $model)->FindForPassport($username);
        }else{
            $user = (new $model)->where('email', $username)->first();
        }

        if(!$user){
            return;
        }elseif(method_exists($model, 'validateForPassportGrant')){
            if(!$user->validateForPassportGrant($password)){
                return;
            }
        }elseif(!$user->haser->check($password, $user->getAuthPassword())){
            return;
        }

        $identifier = $user->identifier?:$user->id;
        return new User($identifier);
    }
}
