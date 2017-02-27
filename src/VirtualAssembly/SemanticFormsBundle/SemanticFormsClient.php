<?php

namespace VirtualAssembly\SemanticFormsBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class SemanticFormsClient
{
    var $baseUrlFoaf = 'http://xmlns.com/foaf/0.1/';

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
            'base_uri'        => 'http://'.$this->domain,
            'timeout'         => $this->timeout,
            'allow_redirects' => true,
          ]
        );
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
        return json_decode($this->get($path, $options),true);
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

    public function send($data)
    {
        $user     = $this->login;
        $password = $this->password;

        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->server.$this->baseLinkLoginAction);
        //curl_setopt($ch,CURLOPT_POST, true);
        // TODO : use the account of the user
        // TODO Migrate into SF bundle.
        curl_setopt(
          $ch,
          CURLOPT_POSTFIELDS,
          "userid=".$user."&password=".$password
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt(
          $ch,
          CURLOPT_COOKIEJAR,
          dirname(__DIR__).'cookie/'.$this->getUser()->getUsername().'.txt'
        );
        //execute post
        curl_exec($ch);
        curl_setopt($ch, CURLOPT_URL, $this->server.$this->baseLinkSaveAction);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt(
          $ch,
          CURLOPT_COOKIEJAR,
          dirname(__DIR__).'cookie/'.$this->getUser()->getUsername().'.txt'
        );
        curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //close connection
        curl_close($ch);

        return $info;
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
