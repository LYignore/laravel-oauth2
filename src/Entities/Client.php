<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\ClientEntityInterface;
use Lyignore\LaravelOauth2\Design\Grant\AuthCodeGrant;
use Lyignore\LaravelOauth2\Design\Grant\ClientCredentialsGrant;
use Lyignore\LaravelOauth2\Design\Grant\PasswordGrant;

class Client implements ClientEntityInterface, \ArrayAccess
{
    const CREDENTIALS_CLIENT = ClientCredentialsGrant::IDENTIFIER;

    const PASSWORD_CLIENT = PasswordGrant::IDENTIFIER;

    const AUTHORIZATION_CLIENT = AuthCodeGrant::IDENTIFIER;

    public $name;

    public $redirectUri = 'http://loacalhost';

    public $identifier;

    public $validAt;

    protected $secret;

    protected $privateKey;

    protected $publicKey;

    public $grantType = self::CREDENTIALS_CLIENT;

    public $scopes;

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setGrantType($grantType)
    {
        $this->grantType = $grantType;
    }

    public function getGrantType()
    {
        return $this->grantType;
    }

    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    public function getAuthIdentifier()
    {
        return $this->identifier;
    }

    public function setVaildUntil(\DateTime $dateTime)
    {
        $this->validAt = $dateTime;
    }

    public function getVaildUntil()
    {
        return $this->validAt;
    }

    public function __toString()
    {
        return json_encode([
            'name' => $this->name,
            'identifier' => $this->identifier,
            'secret' => $this->secret,
            'scopes' => $this->scopes,
            'grant_type' => $this->grantType,
            'public_key' => $this->publicKey,
            'redirect_uri'=> $this->redirectUri,
            'valid_at'    => $this->validAt,
        ]);
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset)?$this->{$offset}:null;
    }

    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)){
            $this->{$offset} = null;
        }
    }
}