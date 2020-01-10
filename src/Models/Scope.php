<?php
namespace Lyignore\LaravelOauth2\Models;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    protected $hidden = ["secret"];

    protected $table = "oauth_scopes";

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $dates = ["expires_at"];

    public static function findScopes($scopes)
    {
        $keys = strstr($scopes, '*', true);
        $scopeResult = [];
        if($keys){
            $scopesArr = self::where('id', 'like', $keys.'%')->get();
            foreach ($scopesArr as $value){
                $scopeResult[] = $value['id'];
            }
        }else{
            $scope = self::where('id', $scopes)->first();
            if($scope){
                $scopeResult[] = $scope['id'];
            }
        }
        return $scopeResult;
    }
}
