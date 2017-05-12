<?php

namespace GrandsVoisinsBundle\Tests\Controller;


use GrandsVoisinsBundle\GrandsVoisinsConfig;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class WebserviceControllerTest extends toolsTest
{
    var $entities = [
      SemanticFormsBundle::URI_FOAF_ORGANIZATION,
      SemanticFormsBundle::URI_FOAF_PERSON,
      SemanticFormsBundle::URI_FOAF_PROJECT,
      SemanticFormsBundle::URI_PURL_EVENT,
      SemanticFormsBundle::URI_FIPA_PROPOSITION,
    ];
    public function testWebserviceParameters(){
        //not logged
        $this->crawler = $this->client->request('GET', '/webservice/parameters');
        self::assertTrue(
          $this->client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
          )
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent(),true);
        self::assertArrayHasKey('access', $jsonResponse);
        self::assertEquals('anonymous', $jsonResponse['access']);

        self::assertArrayHasKey('name', $jsonResponse);
        self::assertEmpty($jsonResponse['name']);
        self::assertArrayHasKey('buildings', $jsonResponse);
        foreach ($jsonResponse['buildings'] as $building => $detail){
            self::assertArrayHasKey($building,GrandsVoisinsConfig::$buildingsExtended);
            self::assertArrayHasKey('title',$detail);
        }
        self::assertArrayHasKey('entities', $jsonResponse);
        foreach ($this->entities as $uri){
            self::assertArrayHasKey($uri,$jsonResponse['entities']);
            self::assertArrayHasKey('name',$jsonResponse['entities'][$uri]);
            self::assertArrayHasKey('plural',$jsonResponse['entities'][$uri]);
            self::assertArrayHasKey('icon',$jsonResponse['entities'][$uri]);
            self::assertArrayHasKey('type',$jsonResponse['entities'][$uri]);
        }
        self::assertArrayHasKey('thesaurus', $jsonResponse);
        self::assertGreaterThan(0,$jsonResponse['thesaurus']);
        foreach ($jsonResponse['thesaurus'] as $detail){
            self::assertArrayHasKey('uri',$detail);
            self::assertArrayHasKey('label',$detail);
        }
        //logged
        $this->testLogin();
        $this->crawler = $this->client->request('GET', '/webservice/parameters');
        self::assertTrue(
          $this->client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
          )
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent(),true);
        self::assertArrayHasKey('access', $jsonResponse);
        self::assertNotEquals('anonymous', $jsonResponse['access']);
        self::assertArrayHasKey('name', $jsonResponse);
        self::assertEquals($this->user, $jsonResponse['name']);
        $this->testLogout();
    }



}
