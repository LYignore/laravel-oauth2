<?php
namespace Lyignore\LaravelOauth2\Models;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Scope implements Arrayable, Jsonable
{
    public $id;

    public $description;

    public function __construct($id, $description)
    {
        $this->id = $id;
        $this->description = $description;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}