<?php

namespace VirtualAssembly\SemanticFormsBundle;

use \GuzzleHttp\Client;

class SemanticFormsClient
{
    function auth($login, $password)
    {
        $client = new Client();
        // TODO
    }

    /**
     * Retrieve simple json data.
     *
     * @param $url
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function httpLoadJson($url)
    {
        $client = new Client();
        $result = $client->request('GET', $url);

        return $result->getBody();
    }
}
