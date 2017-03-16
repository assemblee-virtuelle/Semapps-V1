<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebserviceController extends Controller
{

    public function buildingAction()
    {
        return new JsonResponse(GrandsVoisinsConfig::$buildings);
    }

    public function searchAction(Request $request)
    {
        $term = $request->query->get('t');
        // Build a fake empty response in case of fail.
        $output = (object)['results' => []];

        if ($term) {
            $sfClient = $this->container->get('semantic_forms.client');

            // Common fields.
            $fields = [
              'title'    => 'foaf:name',
              'image'    => 'foaf:img',
              'type'     => 'rdf:type',
              'subject'  => 'purl:subject',
              'building' => 'gvoi:building',
            ];

            $requestSelect = '';
            $requestFields = '';
            // Add optional fields.
            foreach ($fields as $alias => $type) {
                $requestSelect .= ' ?'.$alias;
                $requestFields .= 'OPTIONAL { ?ORGA '.$type.' ?'.$alias.' } ';
            }

            $request = 'SELECT ?uri '.$requestSelect.' '.
              'WHERE { '.
              '  GRAPH ?GR { '.
              '    ?uri text:query "'.$term.'" . '.
              '    ?uri rdf:type <http://xmlns.com/foaf/0.1/Organization> . '.$requestFields;

            $request .= '}}';

            // Search
            $response = $sfClient->sparql(
            // Use common and custom prefixes.
              $sfClient->prefixesCompiled."\n\n ".
              // Query.
              $request
            );

            // It can be a DNS problem, but we deep look about timeouts.
            if ($response instanceof RequestException) {
                $output->error = 'TIMEOUT';
            } // Success
            else if (is_array(
                $response
              ) && isset($response['results']['bindings'])
            ) {
                $resultsFiltered = [];
                foreach ($response['results']['bindings'] as $index => $result) {
                    $item = [];
                    foreach ($result as $fieldName => $data) {
                        $item[$fieldName] = $data['value'];
                    }
                    $resultsFiltered[$index] = $item;
                }
                $output->results = $resultsFiltered;
            } // We don't really know what happened.
            else {
                $output->error = 'ERROR';
            }
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

    public function requestPair($uri)
    {

        $sfClient = $this->container->get('semantic_forms.client');

        // All properties about organization.
        $properties = $sfClient
          ->sparqlData(
            'CONSTRUCT { '.
            '<'.$uri.'> ?P ?O . '.
            '} WHERE { GRAPH ?G { '.
            '<'.$uri.'> ?P ?O . '.
            '}}'
          )->fields;

        // Things pointing to the item.
        $related = $sfClient
          ->sparqlData(
            'CONSTRUCT { '.
            '?S ?P1 <'.$uri.'> . '.
            ' } WHERE { GRAPH ?G { '.
            '?S ?P1 <'.$uri.'> . '.
            ' }}'
          )->fields;

        return [
          'properties' => $properties,
          'related'    => $related,
        ];
    }
}
