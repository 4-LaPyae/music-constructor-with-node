<?php

namespace App\Hashingoperations;

class HashingOperations
{
    public static function encrypt($privateKey, $secretKey, $encryptMethod = 'AES-256-CBC', $string)
    {
        // $key = hash('sha256', $privateKey);
        // $ivalue = substr(hash('sha256', $secretKey), 0, 16);
        $key =  $privateKey;
        $ivalue = substr($secretKey, 0, 16);
        $result = openssl_encrypt($string, $encryptMethod, $key, 0, $ivalue);
        return base64_encode($result);
    }

    public static function decrypt($privateKey, $secretKey, $encryptMethod = 'AES-256-CBC', $stringEncrypt)
    {
        // $key    = hash('sha256', $privateKey);
        // $ivalue = substr(hash('sha256', $secretKey), 0, 16);
        $key =  $privateKey;
        $ivalue = substr($secretKey, 0, 16);
        return openssl_decrypt(base64_decode($stringEncrypt), $encryptMethod, $key, 0, $ivalue);
    }
}
