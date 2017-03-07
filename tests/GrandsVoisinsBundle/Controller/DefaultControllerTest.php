<?php

namespace GrandsVoisinsBundle\Tests\Controller;

require_once '../../../src/Wexample/Test/Symfony3/WebTestCase.php';

use Wexample\Test\Symfony3\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());

        $this->assert(true);
    }
}
