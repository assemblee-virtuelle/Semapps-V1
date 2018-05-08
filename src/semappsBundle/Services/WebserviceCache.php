<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 26/12/17
 * Time: 16:47
 */

namespace semappsBundle\Services;

use FOS\UserBundle\Util\TokenGenerator;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class WebserviceCache
{
    private $cache;
    public function __construct($cacheDir)
    {
        $this->cache = new FilesystemAdapter('cache.webservice',0,$cacheDir);
    }

    public function setContent($serviceName,$uri,$content){
        $service = $this->cache->getItem('semapps.'.$serviceName);
        $arrayContent = $service->get();
        $arrayContent[$uri] = $content ;
        $service->set($arrayContent);
        $this->cache->save($service);

    }
    public function getContent($serviceName,$uri){
        $service = $this->cache->getItem('semapps.'.$serviceName);
        $arrayContent = $service->get();
        $data = false;
        if(is_array($arrayContent) && array_key_exists($uri, $arrayContent)){
                $data = $arrayContent[$uri];
        }
        return $data;
    }


}