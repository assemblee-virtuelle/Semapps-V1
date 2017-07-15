<?php
namespace  VirtualAssembly\SparqlBundle\Sparql;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;

/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/05/2017
 * Time: 11:36
 */
class sparqlUpdate extends sparql
{
    private $type;

    private $delete;

    private $insert;


    public function __construct($type)
    {
        $this->type =$type;
    }

    public function addDelete($subject,$predicate,$object,$graph =null){
        if( $this->type == SparqlClient::SPARQL_DELETE_DATA || $this->type == SparqlClient::SPARQL_DELETE || $this->type == SparqlClient::SPARQL_DELETE_INSERT)
            $this->formatTriple($this->delete,$this->createTriple($subject,$predicate,$object),$graph);
        return $this;
    }

    public function addInsert($subject,$predicate,$object,$graph =null){
        if( $this->type == SparqlClient::SPARQL_INSERT_DATA || $this->type == SparqlClient::SPARQL_INSERT || $this->type == SparqlClient::SPARQL_DELETE_INSERT)
            $this->formatTriple($this->insert,$this->createTriple($subject,$predicate,$object),$graph);
        return $this;
    }



    public function getQuery()
    {
        $data = parent::getQuery();
        $query= "";
        switch ($this->type){
            case SparqlClient::SPARQL_DELETE_DATA :
                if (sizeof($this->delete) > 0){
                    $query = 'DELETE DATA {';
                    $query .= $this->formatTab($this->delete);
                    $query .= "}";
                }
                break;
            case SparqlClient::SPARQL_INSERT_DATA:
                if (sizeof($this->insert) > 0){
                    $query = 'INSERT DATA {';
                    $query .= $this->formatTab($this->insert);
                    $query .= "}";
                }
                break;
            case SparqlClient::SPARQL_DELETE :
                if (sizeof($this->delete) > 0){
                    $query = 'DELETE {';
                    $query .= $this->formatTab($this->delete);
                    $query .= "}";
                }
                break;
            case SparqlClient::SPARQL_INSERT :
                if (sizeof($this->insert) > 0){
                    $query = 'INSERT {';
                    $query .= $this->formatTab($this->insert);
                    $query .= "}";
                }
                break;
            case SparqlClient::SPARQL_DELETE_INSERT :
                $query ="";
                if (sizeof($this->delete) > 0){
                    $query = 'DELETE {';
                    $query .= $this->formatTab($this->delete);
                    $query .= "}";
                }
                $query .= ' ';
                if (sizeof($this->insert) > 0){
                    $query .= 'INSERT {';
                    $query .= $this->formatTab($this->insert);
                    $query .= "}";
                }
                break;
        }

        return $data["prefix"].$query.$data["where"];
    }



}