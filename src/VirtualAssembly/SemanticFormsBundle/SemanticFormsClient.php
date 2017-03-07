<?php

namespace VirtualAssembly\SemanticFormsBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;

class SemanticFormsClient
{
    var $baseUrlFoaf = 'http://xmlns.com/foaf/0.1/';
    var $specsMap = [
      'Person' => 'http://raw.githubusercontent.com/jmvanel/semantic_forms/master/vocabulary/forms#personForm',
    ];
    var $cookieName = 'cookie.txt';

    public function __construct($domain, $login, $password, $timeout)
    {
        $this->domain   = $domain;
        $this->login    = $login;
        $this->password = $password;
        $this->timeout  = $timeout;
    }

    public function buildClient($cookie = "")
    {
        return new Client(
          [
            'base_uri'        => 'http://'.$this->domain,
            'timeout'         => $this->timeout,
            'allow_redirects' => true,
            'cookies'         => $cookie,
          ]
        );
    }

    public function post($path, $options = [])
    {
        $cookie = new FileCookieJar($this->cookieName, true);
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

        // Useful for debug to have full URL.
        $options['on_stats'] = function (TransferStats $stats) use (&$url) {
            $url = $stats->getEffectiveUri();
        };

        $options['headers'] = [
            // Sign request.
          'User-Agent' => 'GrandsVoisinsBundle',
            // Ensure to get JSON response.
          'Accept'     => 'application/json',
        ];

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
        return json_decode($this->get($path, $options), JSON_OBJECT_AS_ARRAY);
    }

    public function auth($login = null, $password = null)
    {
        $login    = $login ? $login : $this->login;
        $password = $password ? $password : $this->password;
        $options  = array(
          'query' => array(
            'userid'   => $login,
            'password' => $password,
          ),
        );
        $cookie   = new FileCookieJar($this->cookieName, true);
        $client   = $this->buildClient($cookie);
        try {
            $response = $client->request(
              'GET',
              '/authenticate',
              $options
            );

            return $response->getStatusCode();
        } catch (RequestException $e) {
            return $e;
        }
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
        return json_decode(
          $this->get(
            '/lookup',
            [
              'query' => [
                'QueryString' => $term,
                'QueryClass'  => $class ? $class : '',
              ],
            ]
          )
        );
    }

    public function send($data, $login, $password)
    {
        $data = $this->format($data);
        $this->auth($login, $password);
        $response = $this->post(
          '/save',
          [
            'form_params' => $data,
          ]
        );

        return $response->getStatusCode();
    }

    public function createSpec($specUrl)
    {
        return $this->getJSON(
          '/create-data',
          [
            'query' => [
              'uri' => $specUrl,
            ],
          ]
        );
    }

    public function createSpecType($specType)
    {
        return $this->createSpec($this->specsMap[$specType]);
    }

    public function createFoaf($foafType)
    {
        return $this->createSpec($this->baseUrlFoaf.$foafType);
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

    private function format($data)
    {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            if (is_array($value)) {
                foreach ($value as $newValue) {

                    $temp = explode('+', $key);
                    $temp[0] .= '+';
                    $temp[1] .= '+';
                    if (strpos($temp[2], '<') !== false) {
                        $temp[2] = '<'.$newValue.'>+';
                    } else {
                        $temp[2] = '"'.$newValue.'"+';
                    }
                    $data[str_replace(
                      "_",
                      '.',
                      urldecode(implode($temp))
                    )] = $newValue;
                }
            } else {
                $data[str_replace("_", '.', urldecode($key))] = $value;
            }
        }

        return $data;
    }

}
