<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 12/05/2017
 * Time: 11:11
 */

namespace coreBundle\Tests\Controller;


class EventControllerTest extends toolsFormTest
{
    protected $nameForm = 'event';
    protected $formButtonName = 'Enregistrer';
    protected $createButton = 'Ajouter';
    protected $editButton = 'Editer';
    protected $route = '/mon-compte/evenement';
    protected $tabValue = [
      'label' => [
        'label',
        'label_update',
        'label' // obligatoire
      ],
      'description' => [
        'description',
        'description_update'
      ],
      'shortDescription' => [
        'shortDescription',
        'shortDescription_update'
      ],
      'room' => [
        'room',
        'room_update'
      ],
      'mbox' => [
        'mbox@mbox.com',
        'mbox_update@mbox.com'
      ],
    ];

    public function goBack(){
        $this->setForm($this->nameForm,$this->tabValue,toolsFormTest::TEST_CREATE);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
    }

    public function testEventCreateAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        $this->crawler = $this->client->followRedirect();
        //$this->debugContent();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->createButton.'")')->count());
        $this->crawler = $this->client->click($this->crawler->selectLink($this->createButton)->link());
        //$this->debugContent();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue,toolsFormTest::TEST_CREATE);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();

        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->editButton.'")')->count());
        //$this->info( $this->crawler->filter('td')->children());
        //$link = $this->crawler->filter('td')->first()->children()->link();
        $this->crawler = $this->client->click($this->crawler->filter('a:contains("Editer")')->first()->link());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue,toolsFormTest::TEST_CREATE);
        $this->testLogout();

    }

    public function testEventUpdateAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->editButton.'")')->count());
        $this->crawler = $this->client->click($this->crawler->filter('a:contains("Editer")')->first()->link());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue,toolsFormTest::TEST_UPDATE);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();

        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->editButton.'")')->count());
        //$this->info( $this->crawler->filter('td')->children());
        //$link = $this->crawler->filter('td')->first()->children()->link();
        $this->crawler = $this->client->click($this->crawler->filter('a:contains("Editer")')->first()->link());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue,toolsFormTest::TEST_UPDATE);
        $this->goBack();
        $this->testLogout();

    }

    public function testEventDeleteAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->editButton.'")')->count());
        $this->crawler = $this->client->click($this->crawler->filter('a:contains("Editer")')->first()->link());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();

        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->editButton.'")')->count());
        //$this->info( $this->crawler->filter('td')->children());
        //$link = $this->crawler->filter('td')->first()->children()->link();
        $this->crawler = $this->client->click($this->crawler->filter('a:contains("Editer")')->first()->link());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue);
        $this->goBack();
        $this->testLogout();
    }
}