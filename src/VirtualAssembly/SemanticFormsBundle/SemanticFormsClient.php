<?php

namespace VirtualAssembly\SemanticFormsBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class SemanticFormsClient
{
    public function __construct($domain, $login, $password, $timeout)
    {
        $this->domain   = $domain;
        $this->login    = $login;
        $this->password = $password;
        $this->timeout  = $timeout;
    }

    public function buildClient()
    {
        return new Client(
          [
            'baseuri'         => 'http://'.$this->domain,
            'timeout'         => $this->timeout,
            'allow_redirects' => true,
          ]
        );
    }

    public function auth($login, $password)
    {
        $client = new Client();
        // TODO use guzzle for authentication.
    }

    public function getSemanticFormsUrl()
    {
        return 'http://'.$this->domain;
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

    public function search($term, $class = false)
    {
        $client = $this->buildClient();

        try {
            $response = $client->request(
              'GET',
              $this->getSemanticFormsUrl().'/lookup',
              [
                'query' => [
                  'QueryString' => $term,
                  'QueryClass'  => $class,
                ],
              ]
            );

            return $response->getBody();
        } catch (RequestException $e) {
            return $e;
        }
    }
}
