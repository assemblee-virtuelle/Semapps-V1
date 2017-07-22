<?php

namespace mmmfestBundle\Tests\Controller;


class OrganisationControllerTest extends toolsFormTest
{
    protected $nameForm = 'organization';
    protected $formButtonName = 'Enregistrer';
    protected $route = '/orga/detail';
    protected $tabValue = [
      'name' => [
        'name',
        'name_update'
      ],
      'administrativeName' => [
        'administrativeName',
        'administrativeName_update'
      ],
      'description' => [
        'description',
        'description_update'
      ],
      'shortDescription' => [
        'shortDescription',
        'shortDescription_update'
      ],
      'conventionType' => [
        'conventionType',
        'conventionType_update'
      ],
      'employeesCount' => [
        10,
        20,
        0
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
      'twitter' => [
        'http://twitter',
        'http://twitter_update'
      ],
      'linkedin' => [
        'http://linkedin.com',
        'http://linkedin_update.com'
      ],
      'facebook' => [
        'http://facebook.com',
        'http://facebook_update.com'
      ],
      'contributionType' => [
        'http://contributionType.com',
        'http://contributionType_update.com'
      ],
    ];

    public function testOrganizationCreateAction(){
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

    public function testOrganizationUpdateAction(){
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
        $this->testOrganizationCreateAction();
        $this->testLogout();
    }

    public function testOrganizationDeleteAction(){
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
        $this->testOrganizationCreateAction();
        $this->testLogout();
    }
}
