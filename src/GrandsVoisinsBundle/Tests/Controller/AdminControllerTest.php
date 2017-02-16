<?php

namespace GrandsVoisinsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{

    public function testTest()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'testRoute');
    }

}
