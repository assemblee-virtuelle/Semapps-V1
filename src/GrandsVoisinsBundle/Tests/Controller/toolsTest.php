<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 12/05/2017
 * Time: 10:16
 */

namespace GrandsVoisinsBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class toolsTest extends WebTestCase
{
    protected $user = 'test_php';
    protected $password = 'tPaqskxgPDEH';
    /** @var $client \Symfony\Bundle\FrameworkBundle\Client */
    protected $client = null;
    /**@var $crawler \Symfony\Component\DomCrawler\Crawler*/
    protected $crawler = null;

    public function setUp(){
        $this->logout();
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

    public function getUri(){
        $this->crawler = $this->client->request('GET', "/mon-compte/profile");
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Voir")')->count());
        $uri = explode("uri=",$this->crawler->filter('a:contains("Voir")')->attr('href'))[1];
        return $uri;
    }

    public function log($message, $color = false)
    {
        $output = "\n";
        if ($color) {
            $output .= "\033[1;".$color."m";
        }
        $output .= $message;
        if ($color) {
            $output .= "\033[0m\n";
        }
        echo $output;
    }

    public function warn($message)
    {
        $this->log($message, 33);
    }

    public function info($message)
    {
        $this->log($message, 34);
    }

    public function error($message, $fatal = true)
    {
        $this->log($message, 31);
        if ($fatal) {
            $this->fail($message);
        }
    }

    public function debugContent()
    {
        if (!$this->crawler) {
            $this->error('No crawler found in debug method !');
        }
        $body = $this->crawler->filter('body');
        if ($body) {
            $output = $body->html();
        } else {
            $output = $this->content($this->client);
        }

        echo
          "\n++++++++++++++++++++++++++".
          "\n PATH :".$this->client->getRequest()->getPathInfo().
          "\n CODE :".$this->client->getResponse()->getStatusCode().
          "\n".$output.
          "\n++++++++++++++++++++++++++";
    }

}