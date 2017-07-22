<?php

namespace mmmfestBundle\Tests\Controller;


use mmmfestBundle\mmmfestConfig;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class WebserviceControllerTest extends toolsTest
{
    var $entities = [
      mmmfestConfig::URI_FOAF_ORGANIZATION,
      mmmfestConfig::URI_FOAF_PERSON,
      mmmfestConfig::URI_FOAF_PROJECT,
      mmmfestConfig::URI_PURL_EVENT,
      mmmfestConfig::URI_FIPA_PROPOSITION,
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
            self::assertArrayHasKey($building,mmmfestConfig::$buildingsExtended);
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

    public function testWebserviceSearch(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', '/webservice/search',['t' =>"givenName"]);
        self::assertTrue(
          $this->client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
          )
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent(),true);
        self::assertArrayHasKey('results', $jsonResponse);
        self::assertNotEmpty($jsonResponse);
        $firstElem = $jsonResponse["results"][0];
        self::assertNotEmpty('results', $firstElem);
        self::assertArrayHasKey('familyName', $firstElem);
        self::assertArrayHasKey('givenName', $firstElem);
        self::assertArrayHasKey('title', $firstElem);
        self::assertArrayHasKey('type', $firstElem);
        self::assertArrayHasKey('uri', $firstElem);
        self::assertEquals("givenName",$firstElem["givenName"]);

    }

    public function testWebserviceFieldUriSearch(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', '/webservice/search/field-uri',['QueryString' =>"givenName",'rdfType' =>$this->entities[1]]);
        self::assertTrue(
          $this->client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
          )
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent(),true);
        self::assertNotEmpty($jsonResponse);
        self::assertContains("givenName",reset($jsonResponse));
    }

    public function testWebserviceFieldUriLabel(){
        $this->testLogin();
        $uri = $this->getUri();
        $this->crawler = $this->client->request('GET', '/webservice/label/field-uri',['uri' =>$uri]);
        self::assertTrue(
          $this->client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
          )
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent(),true);
        self::assertNotEmpty($jsonResponse);
        self::assertContains("givenName",reset($jsonResponse));
    }


}
