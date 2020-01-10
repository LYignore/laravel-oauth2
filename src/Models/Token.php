<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = "oauth_access_tokens";

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $dates = ["expires_at"];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        $provider = config("auth.guards.api.provider");

        return $this->belongsTo(config("auth.providers". $provider . ".model"));
    }

    public function can($scope)
    {
        return in_array('*', $this->scopes) || array_key_exists($scope, array_flip($this->scopes));
    }

    public function revoke()
    {
        $this->forceFill(['revoked' => true])->save();
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }
}