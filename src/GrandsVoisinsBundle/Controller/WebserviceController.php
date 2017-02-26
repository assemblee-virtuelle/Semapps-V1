<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebserviceController extends Controller
{

    function getSemanticFormsUrl()
    {
        return 'http://'.$this->getParameter('semantic_forms.domain');
    }

    public function buildingAction()
    {
        // TODO return data from SF ?
        return new JsonResponse(
          [
            "maisonDesMedecins" => "Maison des médecins",
            "lepage"            => "Lepage",
            "pinard"            => "Pinard",
            "lelong"            => "Lelong",
            "pierrePetit"       => "Pierre Petit",
            "laMediatheque"     => "La Médiathèque",
            "ced"               => "CED",
            "oratoire"          => "Oratoire",
            "colombani"         => "Colombani",
            "laLingerie"        => "La Lingerie",
            "laChaufferie"      => "La Chaufferie",
            "robin"             => "Robin",
            "pasteur"           => "Pasteur",
            "jalaguier"         => "Jalaguier",
            "rapine"            => "Rapine",
          ]
        );
    }

    public function searchAction(Request $request)
    {
        $term = $request->query->get('t');
        // Build a fake empty response in case of fail.
        $output = (object)['results' => []];

        if ($term) {
            $timeout = $this->getParameter('semantic_forms.timeout');
            $curl    = curl_init();
            curl_setopt(
              $curl,
              CURLOPT_URL,
              $this->getSemanticFormsUrl().'/lookup?QueryString='.urlencode(
                $term
              )
            );
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            $response = json_decode(curl_exec($curl));
            // No error happened.
            if (!curl_errno($curl)) {
                $output = $response;
            } else {
                $output->error = 'TIMEOUT';
            }
            curl_close($curl);
        }
        return new JsonResponse($output);
    }
}
