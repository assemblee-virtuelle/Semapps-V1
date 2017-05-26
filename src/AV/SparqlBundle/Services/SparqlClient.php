<?php

namespace AV\SparqlBundle\Services;

use AV\SparqlBundle\Sparql\sparqlSelect;
use AV\SparqlBundle\Sparql\sparqlConstruct;
use AV\SparqlBundle\Sparql\sparqlUpdate;
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/05/2017
 * Time: 11:05
 */
class SparqlClient
{
    const SPARQL_SELECT = 0;
    const SPARQL_CONSTRUCT = 1;
    const SPARQL_DELETE = 2;
    const SPARQL_DELETE_DATA = 3;
    const SPARQL_INSERT = 4;
    const SPARQL_INSERT_DATA = 5;
    const SPARQL_DELETE_INSERT = 6;

    private $type = null;

    public function newQuery($type){
        switch ($type){
            case SparqlClient::SPARQL_SELECT:
                return new sparqlSelect();
                break;
            case SparqlClient::SPARQL_CONSTRUCT:
                return new sparqlConstruct();
                break;
            case SparqlClient::SPARQL_DELETE:
                return new sparqlUpdate($type);
                break;
            case SparqlClient::SPARQL_DELETE_DATA:
                return new sparqlUpdate($type);
                break;
            case SparqlClient::SPARQL_INSERT:
                return new sparqlUpdate($type);
                break;
            case SparqlClient::SPARQL_INSERT_DATA:
                return new sparqlUpdate($type);
                break;
//            case SparqlClient::SPARQL_DELETE_INSERT:
//                return ''
//                break;
        }
        return false;
    }

}