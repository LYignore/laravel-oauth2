<?php
namespace Lyignore\LaravelOauth2\Models;

class Scope implements Models
{
    protected $table = "oauth_scope";

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $dates = ["expires_at"];
}
