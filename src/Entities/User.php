<?php
namespace Lyignore\LaravelOauth2\Entities;

use Lyignore\LaravelOauth2\Design\Entities\UserEntityInterface;

class User implements UserEntityInterface, \ArrayAccess
{
    public $identifier;

    public function __construct($idnetifier)
    {
        $this->setIdentifier($idnetifier);
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
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

    public function __toString()
    {
        return json_encode([
            'identifier' => $this->identifier,
        ]);
    }
}