<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 28/11/2017
 * Time: 11:48
 */

namespace semappsBundle\Services;


use semappsBundle\semappsConfig;

class confManager
{
    private $personConf;
    private $organizationConf;
    private $projectConf;
    private $eventConf;
    private $proposalConf;
    private $documentConf;
    private $documenttypeConf;
    private $projecttypeConf;
    private $eventtypeConf;
    private $proposaltypeConf;
    private $dbpedia;
    public function __construct(	 $personConf, $organizationConf, $projectConf, $eventConf,
                                     $proposalConf, $documentConf, $documenttypeConf,$dbpedia,
                                     $projecttypeConf,$eventtypeConf,$proposaltypeConf){
        $this->personConf = $personConf;
        $this->organizationConf = $organizationConf;
        $this->projectConf = $projectConf;
        $this->eventConf = $eventConf;
        $this->proposalConf = $proposalConf;
        $this->documentConf = $documentConf;
        $this->documenttypeConf = $documenttypeConf;
        $this->dbpedia = $dbpedia;
        $this->projecttypeConf = $projecttypeConf;
        $this->eventtypeConf = $eventtypeConf;
        $this->proposaltypeConf = $proposaltypeConf;
    }
    public function getConf($type = null){

        $conf = null;
        switch ($type){
            case semappsConfig::URI_PAIR_PERSON:
                $conf = $this->personConf;
                break;
            case semappsConfig::URI_PAIR_ORGANIZATION:
                $conf = $this->organizationConf;
                break;
            case semappsConfig::URI_PAIR_PROJECT:
                $conf = $this->projectConf;
                break;
            case semappsConfig::URI_PAIR_EVENT:
                $conf = $this->eventConf;
                break;
            case semappsConfig::URI_PAIR_PROPOSAL:
                $conf = $this->proposalConf;
                break;
            case semappsConfig::URI_PAIR_DOCUMENT:
                $conf = $this->documentConf;
                break;
            case semappsConfig::URI_PAIR_DOCUMENT_TYPE:
                $conf = $this->documenttypeConf;
                break;
            case semappsConfig::URI_PAIR_PROJECT_TYPE:
                $conf = $this->projecttypeConf;
                break;
            case semappsConfig::URI_PAIR_EVENT_TYPE:
                $conf = $this->eventtypeConf;
                break;
            case semappsConfig::URI_PAIR_PROPOSAL_TYPE:
                $conf = $this->proposaltypeConf;
                break;
            default:
                $conf = $this->dbpedia;
        }
        return $conf;
    }

}