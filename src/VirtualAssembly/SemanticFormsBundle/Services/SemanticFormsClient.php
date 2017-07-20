<?php

namespace VirtualAssembly\SemanticFormsBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;

/**
 * Class SemanticFormsClient
 * @package VirtualAssembly\SemanticFormsBundle\Services
 * classe qui sert d'interface avec semantic-forms
 */
class SemanticFormsClient
{
    //var $baseUrlForm = 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv-forms.ttl#';
    var $cookieName = 'cookie.txt';
    var $prefixes = [
      'xsd'   => '<http://www.w3.org/2001/XMLSchema#>',
      'fn'    => '<http://www.w3.org/2005/xpath-functions#>',
      'text'  => '<http://jena.apache.org/text#>',
      'rdf'   => '<http://www.w3.org/1999/02/22-rdf-syntax-ns#>',
      'rdfs'  => '<http://www.w3.org/2000/01/rdf-schema#>',
      'foaf'  => '<http://xmlns.com/foaf/0.1/>',
      'purl'  => '<http://purl.org/dc/elements/1.1/>',
      'event' => '<http://purl.org/NET/c4dm/event.owl#>',
      'fipa'  => '<http://www.fipa.org/schemas#>',
      'skos'  => '<http://www.w3.org/2004/02/skos/core#>',
    ];
    var $prefixesCompiled = '';
    var $fieldsAliases = [];


    CONST VALUE_TYPE_URI = 1;
    CONST VALUE_TYPE_TEXT = 2;

    /**
     * SemanticFormsClient constructor.
     * @param $domain
     * @param $login
     * @param $password
     * @param $timeout
     * @param array $prefixes
     * @param array $fieldsAliases
     * @param $baseUrlForm
     */
    public function __construct(
      $domain,
      $login,
      $password,
      $timeout,
      $prefixes = [],
      $fieldsAliases = [],
      $baseUrlForm
    ) {

        $this->domain        = $domain;
        $this->login         = $login;
        $this->password      = $password;
        $this->timeout       = $timeout;
        $this->fieldsAliases = $fieldsAliases;
        $this->prefixes      = array_merge($this->prefixes, $prefixes);
        $this->baseUrlForm = $baseUrlForm;
        foreach ($this->prefixes as $key => $uri) {
            $this->prefixesCompiled .= "\nPREFIX ".$key.': '.$uri.' ';
        }
    }

    /**
     * @param string $cookie
     * @return Client
     */
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

    /**
     * @param $path
     * @param array $options
     * @return \Exception|RequestException|mixed|\Psr\Http\Message\ResponseInterface
     * réalise un post sur semantic-forms
     */
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

    /**
     * @param $path
     * @param array $options
     * @return \Exception|RequestException|\Psr\Http\Message\StreamInterface
     * réalise un get sur semantic-forms
     */
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

    /**
     * @param null $login
     * @param null $password
     * @return \Exception|RequestException|int
     * permet de s'authentifier sur semantic-forms
     */
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
     * @param $sparqlRequest
     * @return \Exception|RequestException|\Psr\Http\Message\StreamInterface|string
     * permet d'appeler le service sparql-data de semantic-forms
     * @see post
     */
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

    /**
     * @param $data
     * @param $login
     * @param $password
     * @return int
     * fonction qui permet d'envoyer un formulaire a semantic forms
     * @see auth
     * @see post
     */
    public function send($data, $login, $password)
    {
        $this->auth($login, $password);
        $response = $this->post(
            '/save',
            [
                'form_params' => $data,
            ]
        );

        return $response->getStatusCode();
    }

    /**
     * @param $term
     * @param bool $class
     * @return mixed
     * permet d'appeler le service lookup de semantic-forms
     * @see get
     */
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

    /**
     * @param $specType
     * @return array
     * permet d'appeler le service create-data de semantic-forms
     * @see getJSON
     * @see get
     */
    public function createData($specType)
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

    /**
     * @param $uri
     * @param $specType
     * @return mixed
     * permet d'appeler le service form-data de semantic-forms
     * @see getJSON
     * @see get
     */
    public function formData($uri, $specType)
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

    /**
     * @param $request
     * @return mixed
     */
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
     * @param $query
     * @return mixed
     */
    public function update($query)
    {

        $this->post(
            '/update',
            [
                'form_params' => [
                    'query' => $query,
                ],
            ]
        );
    }

    /**
     * @param $path
     * @param array $options
     * @return mixed
     */
    public function getJSON($path, $options = [])
    {
        return json_decode($this->get($path, $options), JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Iterates over results from sparql request
     * to return only key => value pairs.
     *
     * @param $results
     *
     * @return array
     */
    public function sparqlResultsValues($results)
    {
        $resultsFiltered = [];
        foreach ($results['results']['bindings'] as $index => $result) {
            $item = [];
            foreach ($result as $fieldName => $data) {
                $item[$fieldName] = $data['value'];
            }
            $resultsFiltered[$index] = $item;
        }

        return $resultsFiltered;
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
        $destination[urlencode(
          "<".$subject."> <".$rdfType."> <".
          $type.
          ">."
        )] = $value;
    }


    /**
     * @param $uri
     * @return array
     */
    public function uriProperties($uri)
    {

        // All properties about organization.
        $response =
          $this
            ->sparql(
              'SELECT ?P ?O WHERE { GRAPH ?G { ?S ?P ?O .  <'.$uri.'> ?P ?O }} GROUP BY ?P ?O'
            );

        $output = [
          'uri' => $uri,
        ];

        $prefixesReverted = array_flip($this->fieldsAliases);
        foreach ($response['results']['bindings'] as $item) {
            $key = $item['P']['value'];
            $key = isset($prefixesReverted[$key]) ? $prefixesReverted[$key] : $key;
            if (!isset($output[$key])) {
                $output[$key] = [];
            }
            $output[$key][] = $item['O']['value'];
        }

        return $output;
    }

    /**
     * @param $uri
     * @param string $lang
     * @return bool
     */
    public function dbPediaLabel($uri, $lang = 'en')
    {
        $options            = ['verify' => false];
        $options['headers'] = [
            // Sign request.
          'User-Agent'      => 'GrandsVoisinsBundle',
            // Ensure to get JSON response.
          'Accept'          => 'application/json',
          'Accept-Language' => 'fr',
        ];

        $data = json_decode($this->get($uri, $options));

        if ($data) {
            $key  = 'http://www.w3.org/2000/01/rdf-schema#label';
            $data = $data->$uri->$key;
            // Expected lang.
            $result = $this->dbPediaLabelSearch($data, $lang);
            // English.
            if ($result === false && $lang !== 'en') {
                $result = $this->dbPediaLabelSearch($data, $lang);
            }
            // First value.
            if ($result === false && !empty($data)) {
                $result = current($data)['value'];
            }

            return $result;
        }
    }

    /**
     * @param $data
     * @param $lang
     * @return bool
     */
    public function dbPediaLabelSearch($data, $lang)
    {
        foreach ($data as $item) {
            if ($item->lang === $lang) {
                return $item->value;
            }
        }

        return false;
    }

    /**
     * @param $specType
     * @return string
     */
    public function getSpec($specType)
    {
        return $this->baseUrlForm.$specType;
    }

    /**
     * @param $tab
     * @return array
     */
    private function getValue($tab)
    {
        $temp = array();
        foreach ($tab as $value) {
            array_push($temp, $value['val']['value']);
        }

        return $temp;
    }

    /**
     * @param $type
     * @param int $valeur
     * @return string
     */
    public function formatValue($type = SemanticFormsClient::VALUE_TYPE_TEXT,$valeur ){
        if($type == SemanticFormsClient::VALUE_TYPE_URI)
            return '<'.$valeur.'>';
        else
            return '"'.$valeur.'"';
    }


}
