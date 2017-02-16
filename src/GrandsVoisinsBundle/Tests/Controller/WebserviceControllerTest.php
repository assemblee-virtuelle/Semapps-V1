<?php

namespace GrandsVoisinsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebserviceControllerTest extends WebTestCase
{
    public function testBuilding()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/building');
    }

}
