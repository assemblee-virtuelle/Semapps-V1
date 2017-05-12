<?php

namespace GrandsVoisinsBundle\Tests\Controller;

/* retirer test après : http://dev.wexample.com:9000/update?update=DELETE%20{%20GRAPH%20%3Curn:gv/contacts/new/row/215-org%3E%20{%20?s%20?p%20?o%20.%20}}%20WHERE%20{%20GRAPH%20%3Curn:gv/contacts/new/row/215-org%3E%20{%20?s%20?p%20?o%20.%20}} */

class ProfileControllerTest extends toolsFormTest
{
    protected $nameForm = 'profile';
    protected $formButtonName = 'Enregistrer';
    protected $route = '/mon-compte/profile';
    protected $tabValue = [
      'givenName' => [
        'givenName',
        'givenName_update'
      ],
      'familyName' => [
        'familyName',
        'familyName_update'
      ],
      'homepage' => [
        'http://homepage.com',
        'http://homepage_update.com'
      ],
      'mbox' => [
        'mbox@mbox.com',
        'mbox_update@mbox.com'
      ],
      'phone' => [
        '0123456789',
        '9876543210'
      ],
      'slack' => [
        'slack',
        'slack_update'
      ],
      'postalCode' => [
        '12345',
        '54321'
      ],
    ];

    public function testProfileCreateAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue,toolsFormTest::TEST_CREATE);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue,toolsFormTest::TEST_CREATE);
        $this->testLogout();

    }

    public function testProfileUpdateAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue,toolsFormTest::TEST_UPDATE);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue,toolsFormTest::TEST_UPDATE);
        $this->testProfileCreateAction();
        $this->testLogout();
    }

    public function testProfileDeleteAction(){
        $this->testLogin();
        $this->crawler = $this->client->request('GET', $this->route);
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->setForm($this->nameForm,$this->tabValue);
        $this->client->submit($this->form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$this->formButtonName.'")')->count());
        $this->getForm($this->formButtonName);
        $this->formTest($this->nameForm,$this->tabValue);
        $this->testProfileCreateAction();
        $this->testLogout();
    }
}
