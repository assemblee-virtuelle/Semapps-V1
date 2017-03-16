<?php

namespace VirtualAssembly\SemanticFormsBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;

class SemanticFormsClient
{
    var $baseUrlFoaf = 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv-forms.ttl#';
    var $cookieName = 'cookie.txt';
    var $prefixes = [
      'xsd'  => '<http://www.w3.org/2001/XMLSchema#>',
      'fn'   => '<http://www.w3.org/2005/xpath-functions#>',
      'text' => '<http://jena.apache.org/text#>',
      'rdf'  => '<http://www.w3.org/1999/02/22-rdf-syntax-ns#>',
      'foaf' => '<http://xmlns.com/foaf/0.1/>',
      'purl' => '<http://purl.org/dc/elements/1.1/>',
    ];
    var $prefixesCompiled = '';

    CONST PERSON = 'form-Person';
    CONST ORGANISATION = 'form-Organization';
    CONST PROJET = 'form-Project';
    // preparing
    // CONST EVENT = 'form-Event';
    // CONST PROPOSITION = 'form-Proposition';

    public function __construct(
      $domain,
      $login,
      $password,
      $timeout,
      $prefixes = []
    ) {

        $this->domain   = $domain;
        $this->login    = $login;
        $this->password = $password;
        $this->timeout  = $timeout;
        $this->prefixes = array_merge($this->prefixes, $prefixes);

        foreach ($this->prefixes as $key => $uri) {
            $this->prefixesCompiled .= "\nPREFIX ".$key.': '.$uri.' ';
        }
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
          'User-Agent'      => 'GrandsVoisinsBundle',
            // Ensure to get JSON response.
          'Accept'          => 'application/json',
          'Accept-Language' => 'fr',
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

    public function lookup($term, $class = false)
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

    public function create($specType)
    {
        return $this->getJSON(
          '/create-data',
          [
            'query' => [
              'uri' => $this->getSpec($specType),
            ],
          ]
        );
    }

    public function edit($uri, $specType)
    {
        return $this->getJSON(
          '/form-data',
          [
            'query' => [
              'displayuri' => $uri,
              'formuri'    => $this->getSpec($specType),
            ],
          ]
        );
    }

    public function getSpec($specType)
    {
        return $this->baseUrlFoaf.$specType;
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

    public function sparqlData($sparqlRequest)
    {
        $options['headers'] = [
            // Sign request.
          'User-Agent' => 'GrandsVoisinsBundle',
            // Ensure to get JSON response.
          'Accept'     => 'application/json',
        ];

        try {
            $result = $this->post(
              '/sparql-data',
              [
                'form_params' => [
                  'query' => $sparqlRequest,
                ],
              ]
            );

            return $result->getStatusCode() === 200 ? $result->getBody() : '{}';
        } catch (RequestException $e) {
            return $e;
        }
    }

    public function sparql($request)
    {
        return $this->getJSON(
          '/sparql',
          [
            'query' => [
              'query' => $request,
            ],
          ]
        );
    }

    /**
     * Add a triplet to the given data object.
     *
     * @param        $destination
     * @param        $subject
     * @param        $type
     * @param        $value
     * @param string $rdfType
     */
    public function tripletSet(
      &$destination,
      $subject,
      $type,
      $value,
      $rdfType = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
    ) {
        $destination["<".$subject."> <".$rdfType."> <".
        $type.
        ">."] = $value;
    }

}
