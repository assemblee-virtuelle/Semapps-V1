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
            $response = $sfClient->lookup($term);
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

        $sfClient = $this->container->get('semantic_forms.client');

        // All properties about organization.
        $request    = 'CONSTRUCT { '.
          '<'.$uri.'> ?P ?O . '.
          '} WHERE { GRAPH ?G { '.
          '<'.$uri.'> ?P ?O . '.
          '}}';
        $properties = json_decode($sfClient->sparqlData($request))->fields;

        // Things pointing to the item.
        $request = 'CONSTRUCT { '.
          '?S ?P1 <'.$uri.'> . '.
          ' } WHERE { GRAPH ?G { '.
          '?S ?P1 <'.$uri.'> . '.
          ' }}';

        $related = json_decode($sfClient->sparqlData($request))->fields;

        return [
          'properties' => $properties,
          'related'    => $related,
        ];
    }
}
