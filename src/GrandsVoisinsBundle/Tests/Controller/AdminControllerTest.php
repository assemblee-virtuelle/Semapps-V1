<?php

namespace GrandsVoisinsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private $user = 'rapine';
    private $password = 'rapine';

    /** @var $client \Symfony\Bundle\FrameworkBundle\Client */
    private $client = null;
    /**@var $crawler \Symfony\Component\DomCrawler\Crawler*/
    private $crawler = null;

    public function setUp(){
        $this->logout();
    }

    public function login($user,$password)
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $user,
            'PHP_AUTH_PW'   => $password,
        ));
    }

    public function logout(){
        $this->client = static::createClient();
        $this->crawler = $this->client->request('GET', '/');
    }

    public function testLogin(){
        $this->login($this->user,$this->password);
        $this->crawler = $this->client->request('GET', '/mon-compte/profile');
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Déconnexion")')->count());

    }

    public function testLogout(){
        $this->logout();
        $this->crawler = $this->client->request('GET', '/mon-compte/profile');
        self::assertEquals(0,$this->crawler->filter('html:contains("Déconnexion")')->count());
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Nom d\'utilisateur")')->count());
    }

    public function testHomeAction(){
        $this->login($this->user,$this->password);
        $this->crawler = $this->client->request('GET', '/mon-compte/');
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Déconnexion")')->count());
    }

    public function testProfileAction(){
        $this->login($this->user,$this->password);
        $this->crawler = $this->client->request('GET', '/mon-compte/profile');
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Enregistrer")')->count());
        $buttonCrawlerNode = $this->crawler->selectButton('Enregistrer');
        $form = $buttonCrawlerNode->form();
        //print_r($form);
        $form['profile[givenName]'] = 'givenName';
        $form['profile[familyName]'] = 'familyName';
        $form['profile[homepage]'] = 'http://homepage.com';
        $form['profile[mbox]'] = 'mbox@mbox.com';
        $form['profile[phone]'] = '0123456789';
        $form['profile[slack]'] = 'slack';
        $form['profile[birthday]'] = '1111-11-11';
        $form['profile[postalCode]'] = '12345';
        //print_r($form);
        //print_r($form['profile[postalCode]']);
        //$form['profile[pictureName]']->upload('D:\[IMAGE]\CherDirecteur.png');
        $this->client->submit($form);
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        print_r($this->client->getResponse());
    }
}
