<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasApiTokens,SoftDeletes;
    protected $table = "oauth_clients";

    public $incrementing = false;

    protected $guarded = [];

    protected $hidden = ["secret"];

    public function authCodes()
    {
        return $this->hasMany(AuthCode::class, "client_id");
    }

    public function tokens()
    {
        return $this->hasMany(Token::class, "client_id");
    }

    public function findActive($id)
    {
        $client = $this->where('revoked', false)->find($id);
        return $client;
    }

    public function firstParty()
    {
        return $this->personal_access_client||$this->password_client;
    }

    public static function updateScopes(array $scopes, $identify)
    {
        $scope = self::find($identify);
        $oldScopes = json_decode($scope['scopes'], true);
        $scopes = json_encode(array_unique(array_merge($scopes, $oldScopes)));
        return self::where('id', $identify)->update(['scopes' => $scopes]);
    }
}