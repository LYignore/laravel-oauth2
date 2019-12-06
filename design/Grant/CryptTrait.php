<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

trait CryptTrait
{
    protected $encryptionKey;

    public function setEncryptionKey($key = null)
    {
        $this->encryptionKey = $key;
    }

    protected function encrypt($encryptData)
    {
        try{
            return json_encode($encryptData);
        }catch (\Exception $e){
            throw new \Exception($e);
        }
    }

    protected function decrypt($decryptData)
    {
        try{
            return json_decode($decryptData);
        }catch (\Exception $e){
            throw new \Exception($e);
        }
    }
}