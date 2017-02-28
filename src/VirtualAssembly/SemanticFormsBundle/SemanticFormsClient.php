<?php

namespace VirtualAssembly\SemanticFormsBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class SemanticFormsClient
{
    var $baseUrlFoaf = 'http://xmlns.com/foaf/0.1/';
    var $cookieName = 'cookie.txt';
    public function __construct($domain, $login, $password, $timeout)
    {
        $this->domain   = $domain;
        $this->login    = $login;
        $this->password = $password;
        $this->timeout  = $timeout;
    }

    public function buildClient($cookie ="")
    {
        return new Client(
          [
            'base_uri'        => 'http://'.$this->domain,
            'timeout'         => $this->timeout,
            'allow_redirects' => true,
            'cookies'         => $cookie
          ]
        );
    }

    public function post($path, $options = [])
    {
        $cookie = new FileCookieJar($this->cookieName,true);
        $client = $this->buildClient($cookie);

        try {
            $response = $client->request(
                'POST',
                $path,
                $options
            );

            return $response;
        } catch (RequestException $e) {
            return $e;
        }
    }

    public function get($path, $options = [])
    {
        $client = $this->buildClient();

        try {
            $response = $client->request(
              'GET',
              $path,
              $options
            );

            return $response->getBody();
        } catch (RequestException $e) {
            return $e;
        }
    }

    public function getJSON($path, $options = [])
    {
        return json_decode($this->get($path, $options),JSON_OBJECT_AS_ARRAY);
    }

    public function auth($login, $password)
    {
        $options =array( 'query' => array('userid'=>$login,'password' =>$password));
        $cookie = new FileCookieJar($this->cookieName,true);
        $client = $this->buildClient($cookie);
        // TODO use guzzle for authentication.
        try {
            $response = $client->request(
                'GET',
                '/authenticate',
                $options
            );

            return $response->getBody();
        } catch (RequestException $e) {
            return $e;
        }
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
        return $this->get(
          '/lookup',
          [
            'query' => [
              'QueryString' => $term,
              'QueryClass'  => $class,
            ],
          ]
        );
    }

    public function send($data,$login,$password)
    {
        $this->auth($login,$password);
        $response=$this->post(
            '/save',
            [
                'form_params' => $data
            ]
        );
        return $response->getStatusCode();
    }

    public function createFoaf($foafType)
    {
        return $this->getJSON(
          '/create-data',
          [
            'query' => [
              'uri' => $this->baseUrlFoaf.$foafType,
            ],
          ]
        );
    }

    public function getForm($uri)
    {
        return $this->getJSON(
          '/form-data',
          [
            'query' => [
              'displayuri' => $uri,
            ],
          ]
        );
    }
}
