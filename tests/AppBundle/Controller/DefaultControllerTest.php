<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
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

    public function debugContent($crawler, $client)
    {
        if (!$crawler) {
            $this->error('No crawler found in debug method !');
        }
        $body = $crawler->filter('body');
        if ($body) {
            $output = $body->html();
        } else {
            $output = $this->content($client);
        }

        echo
          "\n++++++++++++++++++++++++++".
          "\n PATH :".$client->getRequest()->getPathInfo().
          "\n CODE :".$client->getResponse()->getStatusCode().
          "\n".$output.
          "\n++++++++++++++++++++++++++";
    }

    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertTrue(
          $crawler->filter('.alert-bobby-inner')->count() === 1
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->debugContent($crawler, $client);

        // Disabling useless redirect.
        //$client->followRedirect();

        /*$this->assertContains(
          'Welcome to Symfony',
          $crawler->filter('#container h1')->text()
        );*/

        //echo $client->getRequest()->getContent();
    }
}
