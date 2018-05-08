<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 28/11/2017
 * Time: 11:48
 */

namespace semappsBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class ConfManager
{
    private $container;
    private $typeToName;
    private $graphToName;
    public function __construct( Container $container,$typeToName,$graphToName){
        $this->container=$container;
        $this->typeToName = $typeToName;
        $this->graphToName = $graphToName;
    }
    public function getConf($types = null,$graph =null){
        if(is_array($types)){
            foreach ($types as $type){
                if(array_key_exists($type,$this->typeToName)){
                    return [ 'key' => $type,
                        'conf' => $this->container->getParameter($this->typeToName[$type].'Conf')
                    ];
                }
            }
        }else{
            if(array_key_exists($types,$this->typeToName)){
                return [ 'key' => $types,
                    'conf' => $this->container->getParameter($this->typeToName[$types].'Conf')
                ];
            }
        }
        if($graph != null){

            foreach ($graph as $key=>$content){
                if(array_key_exists($key,$this->graphToName)){
                    return [ 'key' => $key,
                        'conf' => $this->container->getParameter($this->graphToName[$key].'Conf')
                    ];
                    break;
                }
            }
            return [ 'key' => 'default',
                'conf' =>$this->container->getParameter('dbpediaconf')];
        }
        else{
            return [ 'key' => 'default',
                'conf' =>$this->container->getParameter('dbpediaconf')];

        }
    }

}