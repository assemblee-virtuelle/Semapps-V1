<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebserviceController extends Controller
{
    var $semanticFormsBaseUrl = 'http://semantic-forms.cc:9112/lookup';

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
        $term     = $request->query->get('t');
        // Build a fake empty response in case of fail.
        $response = (object)['results' => []];

        if ($term) {
            $curl = curl_init();
            curl_setopt(
              $curl,
              CURLOPT_URL,
              $this->semanticFormsBaseUrl.'?QueryString='.$term
            );
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = json_decode(curl_exec($curl));
        }

        return new JsonResponse($response);
    }
}
