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
            $response = $sfClient->search($term);
            // It can be a DNS problem, but we deep look about timeouts.
            if ($response instanceof RequestException) {
                $output->error = 'TIMEOUT';
            } // Success
            else if (is_object($response)) {
                $output->results = $response->results;
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
        $request = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '.
          'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> '.
          'prefix foaf: <http://xmlns.com/foaf/0.1/> '.
          'prefix cco: <http://purl.org/ontology/cco/core#> '.
          'CONSTRUCT { '.
          '<'.$uri.'> foaf:givenName ?GN . '.
          '<'.$uri.'> foaf:familyName ?FN . '.
          '<'.$uri.'> foaf:homepage ?H . '.
          '<'.$uri.'> foaf:topic_interest ?TI . '.
          '<'.$uri.'> foaf:knows ?KN . '.
          '<'.$uri.'> foaf:currentProject ?P . '.
          '<'.$uri.'> cco:expertise ?EX . '.
          '} WHERE { GRAPH ?G { '.
          'OPTIONAL { <'.$uri.'> foaf:givenName ?GN } '.
          'OPTIONAL { <'.$uri.'> foaf:familyName ?FN } '.
          'OPTIONAL { <'.$uri.'> foaf:homepage ?H } '.
          'OPTIONAL { <'.$uri.'> foaf:topic_interest ?TI } '.
          'OPTIONAL { <'.$uri.'> foaf:knows ?KN } '.
          'OPTIONAL { <'.$uri.'> foaf:currentProject ?P } '.
          'OPTIONAL { <'.$uri.'> cco:expertise ?EX } } }';

        // URI may point to a place outside SF, but
        // a lot of them should come from here, so we
        // have to authenticate.
        $sfClient = $this->container->get('semantic_forms.client');

        $json = json_decode($sfClient->request($request));

        return isset($json->fields) ? $json->fields : (object)[];
    }
}
