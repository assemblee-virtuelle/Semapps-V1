<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Stream;

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
            }
            else if ($response instanceof Stream) {
                $output->results = $response;
            }
            // We don't really know what happened.
            else {
                $output->error = 'ERROR';
            }
        }

        return new JsonResponse($output);
    }

    public function detailAction(Request $request)
    {
        $output = [];

        // URI may point to a place outside SF, but
        // a lot of them should come from here, so we
        // have to authenticate.
        $sfClient = $this->container->get('semantic_forms.client');

        $sfClient->auth(
          $this->getParameter('semantic_forms.login'),
          $this->getParameter('semantic_forms.password')
        );

        $client = new Client();
        $result = $client->request('GET', $request->query->get('uri'));

        $output['results'] = $result->getBody();

        return new JsonResponse($output);
    }
}
