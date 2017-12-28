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

class contextManager
{
    private $cache;
    private $parameters;
    private $sparqlRepository;
    private $em;
    public function __construct(SparqlRepository $sparqlRepository,EntityManager $em)
    {
        $this->sparqlRepository = $sparqlRepository;
        $this->em = $em;
        $this->cache = new FilesystemAdapter('cache.context');
        $this->parameters = $this->cache->getItem('semapps.context');
    }

    public function getContext($userSflink){
        $parameters = $this->parameters->get();
        if($parameters[$userSflink]){
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
            $organization = $this->em->getRepository('semappsBundle:Organization')->find($contextId);

            $parameters[$userSflink] = [
                'context' => $organization->getGraphURI(),
                'contextId' => $contextId,
                'contextName' => $organization->getName(),
            ];
        }
        $this->parameters->set($parameters);
        $this->cache->save($this->parameters);
    }

    public function getListOfContext($sfLink){
        $listOfGraph = $this->sparqlRepository->getAllowedGraphOfCurrentUser($sfLink);
        $output = [];
        foreach ($listOfGraph as $graph){
            $organization = $this->em->getRepository('semappsBundle:Organization')->findOneBy(['graphURI' => $graph['G']]);
            $output[] = [
                'name' => $organization->getName(),
                'contextId' => $organization->getId()
            ];
        }
        return $output;
    }

}