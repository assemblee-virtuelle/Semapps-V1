<?php

namespace GrandsVoisinsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganisationControllerTest extends WebTestCase
{
    public function testHome()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/orga/home');
    }

    public function testNew()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/orga/new');
    }

}
