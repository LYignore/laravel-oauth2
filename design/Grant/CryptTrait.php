<?php
namespace Lyignore\LaravelOauth2\Design\Grant;

use Lyignore\LaravelOauth2\Design\CryptKey;

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

    protected function makeCryptKey($key, $secondLaravel = null)
    {
        return new CryptKey(
            'file://'.$this->keyPath($key, $secondLaravel),
            null,
            false
        );
    }

    protected function keyPath($file, $secondLevel, $defaultFolder = 'secret')
    {
        $file = ltrim($file, '/\\');
        if(is_null($secondLevel)){
            return storage_path($defaultFolder.DIRECTORY_SEPARATOR.$file);
        }else{
            $secondLevel = ltrim($secondLevel, '/\\');
            $path = storage_path().DIRECTORY_SEPARATOR.$defaultFolder.DIRECTORY_SEPARATOR.$secondLevel;
            if(!is_dir($path)){
                $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true);
                if(!$res){
                    throw new \Exception('Sorry, no permission to create a directory');
                }
            }
            $filePath = $path.DIRECTORY_SEPARATOR.$file;
            return $filePath;
        }
    }
}