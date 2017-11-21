<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 30/06/2017
 * Time: 10:10
 */

namespace semappsBundle\Services;


class Encryption
{
    private $method = 'aes-256-cbc';
    private $option = 0;
    private $secret;
    public function __construct($secret)
    {
        $this->secret =$secret;
    }

    public function encrypt($data){
        $passwordCrypt = openssl_encrypt($data,$this->method,$this->secret,$this->option,substr($this->secret, 0,16));
        return $passwordCrypt;
    }

    public function decrypt($dataEcrypted){
        $passwordDecrypt = openssl_decrypt($dataEcrypted,$this->method,$this->secret,0,substr($this->secret, 0,16));
        return $passwordDecrypt;
    }

}