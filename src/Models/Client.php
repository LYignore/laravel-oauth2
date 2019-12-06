<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = "oauth_clients";

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
        $client = $this->find($id);

        return $client && $client->revoked?:null;
    }

    public function firstParty()
    {
        return $this->personal_access_client||$this->password_client;
    }
}