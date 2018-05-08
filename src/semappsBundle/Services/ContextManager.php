<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 26/12/17
 * Time: 16:47
 */

namespace semappsBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ContextManager
{
    private $cache;
    private $parameters;
    private $sparqlRepository;
    private $em;
    public function __construct($cacheDir,SparqlRepository $sparqlRepository,EntityManager $em)
    {
        $this->sparqlRepository = $sparqlRepository;
        $this->em = $em;
        $this->cache = new FilesystemAdapter('cache.context',0,$cacheDir);
        $this->parameters = $this->cache->getItem('semapps.context');
    }

    public function getContext($userSflink){
        $parameters = $this->parameters->get();
        if($parameters && array_key_exists($userSflink,$parameters)){
            return $parameters[$userSflink];
        }
        else{
            $this->setContext($userSflink,null);
            return $this->getContext($userSflink);
        }
    }

    public function setContext($userSflink,$contextId){
        $parameters = $this->parameters->get();

        if(!$contextId){
            $parameters[$userSflink] = [
                'context' => $userSflink,
                'contextId' => null,
                'contextName' => null,
            ];
        }else{
            $listOfGraph = $this->sparqlRepository->getAllowedGraphOfCurrentUser($userSflink);
            $array = [];
            foreach ($listOfGraph as $content){
                if (!array_key_exists($content['G'],$array) || !$array[$content['G']] ){
                    $array[$content['G']] = $content['O'];
                }
            }

            if(array_key_exists($contextId,$array)){
                $parameters[$userSflink] = [
                    'context' => $contextId,
                    'contextId' => $contextId,
                    'contextName' => $array[$contextId],
                ];
            }

        }
        $this->parameters->set($parameters);
        $this->cache->save($this->parameters);
    }

    public function getListOfContext($sfLink,$userID){
        $listOfGraph = $this->sparqlRepository->getAllowedGraphOfCurrentUser($sfLink);
        $output = [];
        foreach ($listOfGraph as $graph){
            if(array_key_exists('O',$graph)&& $graph['O'] ){
                $output[$graph['G']] = [
                    'name' => $graph['O'],
                    'contextId' => $graph['G']
                ];
            }
        }

        return $output;
    }

    public function actualizeContext($sfLink){
        $actualContext = $this->getContext($sfLink);

        if($actualContext['contextId']){
            $listOfContext=$this->getListOfContext($sfLink,null);
            if(!array_key_exists($actualContext['contextId'],$listOfContext)){
                $this->setContext($sfLink,null);
                return false;
            }
        }
        return true;
    }
}