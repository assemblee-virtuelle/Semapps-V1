<?php
namespace  AV\SparqlBundle\Sparql;
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/05/2017
 * Time: 11:35
 */
class sparqlConstruct extends sparql
{
    private $construct;


    public function addConstruct($subject,$predicate,$object){
        $this->formatTriple($this->construct,$this->createTriple($subject,$predicate,$object),null);
        return $this;
    }
    public function getQuery()
    {
        $data = parent::getQuery();
        $constructString = $this->constructToString();
        return $data["prefix"].$constructString.$data["where"].$data["group"].$data["order"].$data['limit'];
    }

    public function constructToString(){
        $constructString = "";
        if(sizeof($this->construct) > 0){
            $constructString ='CONSTRUCT {';
            $constructString .= $this->formatTab($this->construct);
            $constructString .= '}'."\n";
        }
        return $constructString;
    }

}