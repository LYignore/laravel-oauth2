<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    protected $table = "oauth_auth_codes";

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}