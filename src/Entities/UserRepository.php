<?php
namespace Lyignore\LaravelOauth2\Entities;

use http\Exception\RuntimeException;
use Illuminate\Contracts\Hashing\Hasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The hasher implementation
     */
    protected $hasher;


    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * Get the user identifier by username and password
     *
     */
    public function getUserEntityByUserCredentials($username,
                                                   $password,
                                                   $grantType,
                                                   ClientEntityInterface $clientEntity)
    {
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
        }elseif(method_exists($model, 'validateForPassportPasswordGrant')){
            if(!$user->validateForPassportPasswordGrant($password)){
                return;
            }
        }elseif(!$user->haser->check($password, $user->getAuthPassword())){
            return;
        }

        return new User($user->getAuthIdentifier());
    }
}