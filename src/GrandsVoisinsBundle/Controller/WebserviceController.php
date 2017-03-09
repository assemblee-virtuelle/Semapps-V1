<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;

class WebserviceController extends AbstractController
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
        $output = [];

        // URI may point to a place outside SF, but
        // a lot of them should come from here, so we
        // have to authenticate.
        $sfClient = $this->container->get('semantic_forms.client');

        $sfClient->request('prefix foaf: <http://xmlns.com/foaf/0.1/>
        SELECT DISTINCT *
        WHERE {GRAPH ?G {
        ?S ?P ?O .
        ?S a foaf:Organization .
        }}
        LIMIT 111');

        exit;
    }
}
