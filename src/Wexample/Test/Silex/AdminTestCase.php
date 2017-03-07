<?php

namespace Wexample\Test\Silex;

abstract class AdminTestCase extends WebTestCase
{

    public function newClientLogged()
    {
        $client = $this->newClient();
        $this->logUser('admin', 't9D4PeF@Ty');

        return $client;
    }
}
