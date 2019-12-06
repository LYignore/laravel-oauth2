<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;

class Refresh extends Model
{
    protected $table = "oauth_refresh_tokens";

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $dates = ["expires_at"];

    public function revokeRefreshToken($query, $id)
    {
        return $query->where('id', $id)->forceFill(['revoked' => true])->save();
    }

    public function isAccessTokenRevoked($query, $id)
    {
        return $query->where('id', $id)->where('revoked', true)->exists();
    }
}