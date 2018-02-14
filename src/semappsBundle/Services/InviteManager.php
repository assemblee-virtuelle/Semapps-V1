<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 26/12/17
 * Time: 16:47
 */

namespace semappsBundle\Services;

use FOS\UserBundle\Util\TokenGenerator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class InviteManager
{
    private $cache;
    private $parameters;
    private $tokenGenerator;
    private $cacheDir;
    public function __construct($cacheDir, TokenGenerator $tokenGenerator)
    {
        $this->cache = new FilesystemAdapter('cache.invite',0,$cacheDir);
        $this->parameters = $this->cache->getItem('semapps.invite');
        $this->tokenGenerator = $tokenGenerator;
    }
    public function newInvite($email){

        $parameters = $this->parameters->get();
        $token = $this->tokenGenerator->generateToken();
        $parameters[$email] = $token ;
        $this->parameters->set($parameters);
        $this->cache->save($this->parameters);

        return $token;

    }
    public function verifyInvite($token){
        $email = null;
        $parameters = $this->parameters->get();
        if(is_array($parameters)){
            $parametersFlipped = array_flip($parameters);
            if(array_key_exists($token, $parametersFlipped))
                $email = $parametersFlipped[$token];
        }

        return $email;
    }
    public function getCache(){
        return $this->parameters->get();
    }
    public function removeInvite($email){
        $parameters = $this->parameters->get();
        unset($parameters[$email]);
        $this->parameters->set($parameters);
        $this->cache->save($this->parameters);
    }
}