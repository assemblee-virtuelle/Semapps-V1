<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebserviceController extends Controller
{
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

    public function searchAction()
    {
        return new JsonResponse(
          [
            [
              'title' => 'TestItemOne',
            ],
            [
              'title' => 'TestItemTwo',
            ],
          ]
        );
    }
}
