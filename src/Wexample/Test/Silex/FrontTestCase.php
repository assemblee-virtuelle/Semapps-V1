<?php

namespace Wexample\Test\Silex;

abstract class FrontTestCase extends WebTestCase
{
    var $localePath = '/fr';

    public function newClientLogged()
    {
        $client = $this->newClient();
        $this->logUser('cyril.cordier@gmail.com', 'oenologue2017');

        return $client;
    }
}
