<?php

namespace VirtualAssembly\SemanticFormsBundle;

use \GuzzleHttp\Client;

class SemanticFormsClient
{
    function auth($login, $password)
    {
        echo 'AUTH';
        $client = new Client();
    }
}
