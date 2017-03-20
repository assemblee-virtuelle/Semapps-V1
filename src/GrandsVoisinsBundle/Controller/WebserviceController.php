<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebserviceController extends Controller
{
    var $entitiesParameters = [
      'http://xmlns.com/foaf/0.1/Organization' => [
        'name'   => 'Organisation',
        'plural' => 'Organisations',
        'icon'   => 'tower',
      ],
      'http://xmlns.com/foaf/0.1/Person'       => [
        'name'   => 'Personne',
        'plural' => 'Personnes',
        'icon'   => 'user',
      ],
    ];

    public function __construct()
    {
        // We also need to type as property.
        foreach ($this->entitiesParameters as $key => $item) {
            $this->entitiesParameters[$key]['type'] = $key;
        }
    }

    public function parametersAction()
    {
        return new JsonResponse(
          [
            'buildings' => $this->getBuildings(),
            'entities'  => $this->entitiesParameters,
          ]
        );
    }

    public function getBuildings()
    {
        $sfClient = $this->container->get('semantic_forms.client');
        // Count buildings.
        $response = $sfClient->sparql(
        // Use common and custom prefixes.
          $sfClient->prefixesCompiled."\n\n ".
          // Query.
          'SELECT ?building ( STR(xsd:integer(COUNT(?building))) AS ?count ) '.
          'WHERE { '.
          '  GRAPH ?GR { '.
          // Retrieve building.
          '    ?ORGA gvoi:building ?building . '.
          // Only organizations.
          '    ?ORGA rdf:type <http://xmlns.com/foaf/0.1/Organization> . '.
          '  } '.
          '} '.
          'GROUP BY ?building '.
          'ORDER BY fn:lower-case(?building) '
        );

        $response = $sfClient->sparqlResultsValues($response);

        $buildings = GrandsVoisinsConfig::$buildings;
        foreach ($response as $item) {
            if (isset($buildings[$item['building']])) {
                $buildings[$item['building']]['organizationCount'] = (int)$item['count'];
            }
        }

        return $buildings;
    }

    public function searchSparqlRequest($term)
    {

        $requestSelect = '?uri ';
        $requestFields = '';

        // Required fields.
        $fieldsRequired = [
          'type'  => 'rdf:type',
          'title' => 'foaf:name',
        ];

        foreach ($fieldsRequired as $alias => $type) {
            $requestSelect .= ' ?'.$alias;
            $requestFields .= ' ?ORGA '.$type.' ?'.$alias.' . ';
        }

        // Optional fields.
        $fieldsOptional = [
          'image'    => 'foaf:img',
          'subject'  => 'purl:subject',
          'building' => 'gvoi:building',
        ];

        // Add optional fields.
        foreach ($fieldsOptional as $alias => $type) {
            $requestSelect .= ' ?'.$alias;
            $requestFields .= 'OPTIONAL { ?ORGA '.$type.' ?'.$alias.' } ';
        }

        $requestTypes = '{ ?uri rdf:type <'.implode(
            '> } UNION { ?uri rdf:type <',
            array_keys($this->entitiesParameters)
          ).'> }';

        $request = 'SELECT '.$requestSelect.' '.
          'WHERE { '.
          '  GRAPH ?GR { '.
          // If not term specified, do not filter term.
          ($term ? '    ?uri text:query "'.$term.'" . ' : '').
          // Allowed types.
          $requestTypes.
          // Requested fields.
          $requestFields.
          // Group all duplicated items.
          '}} GROUP BY '.$requestSelect;

        // Use common and custom prefixes.
        return $this->container->get(
          'semantic_forms.client'
        )->prefixesCompiled."\n\n ".
        // Query.
        $request;
    }

    public function searchRequestAction(Request $request)
    {
        // Get term.
        $term = $request->query->get('t');

        // Show request for debug.
        return new Response($this->searchSparqlRequest($term));
    }

    public function searchAction(Request $request)
    {
        $term = $request->query->get('t');
        // Build a fake empty response in case of fail.
        $output = (object)['results' => []];

        $sfClient = $this->container->get('semantic_forms.client');

        // Search
        $response = $sfClient->sparql($this->searchSparqlRequest($term));

        // It can be a DNS problem, but we deep look about timeouts.
        if ($response instanceof RequestException) {
            $output->error = 'TIMEOUT';
        } // Success
        else if (is_array(
            $response
          ) && isset($response['results']['bindings'])
        ) {
            $results = $sfClient->sparqlResultsValues($response);
            // Filter only allowed types.
            $filtered = [];
            foreach ($results as $result) {
                // Type is sometime missing.
                if (isset($result['type']) && isset($this->entitiesParameters[$result['type']])) {
                    $filtered[] = $result;
                }
            }
            $output->results = $filtered;
        }

        return new JsonResponse($output);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function detailAction(Request $request)
    {
        return new JsonResponse(
          (object)[
            'detail' => $this->requestPair($request->get('uri')),
          ]
        );
    }

    public function requestProperties($uri)
    {
        $sfClient = $this->container->get('semantic_forms.client');
        // All properties about organization.
        $response =
          $sfClient
            ->sparql(
              'SELECT ?P ?O WHERE { GRAPH ?G { ?S ?P ?O .  <'.$uri.'> ?P ?O }} GROUP BY ?P ?O'
            );

        $output = [];
        foreach ($response['results']['bindings'] as $item) {
            $key          = $item['P']['value'];
            $output[$key] = $item['O']['value'];
        }

        return $output;
    }

    public function requestPair($uri)
    {

        $output['properties'] = $this->requestProperties($uri);

        switch ($output['properties']['http://www.w3.org/1999/02/22-rdf-syntax-ns#type']) {
            case 'http://xmlns.com/foaf/0.1/Organization':
                $output['responsible'] = $this->requestProperties(
                  $output['properties']['http://virtual-assembly.org/pair_v2#hasResponsible']
                );
                break;
        }

        return $output;

    }
}
