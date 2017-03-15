<?php
/**
 * Created by PhpStorm.
 * User: weeger
 * Date: 27/02/2017
 * Time: 15:49
 */

namespace GrandsVoisinsBundle;


use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;

class GrandsVoisinsConfig
{
    const PREFIX = 'urn:gv/contacts/new/row/';
    const ORGANISATION = 1;
    const TEAM = 2;

    static $buildings = [
      "maisonDesMedecins" => [
        'title' => "Maison des médecins",
        'x'     => 270,
        'y'     => 20,
      ],
      "lepage"            => [
        'title' => "Lepage",
        'x'     => 108,
        'y'     => 70,
      ],
      "pinard"            => [
        'title' => "Pinard",
        'x'     => 330,
        'y'     => 60,
      ],
      "lelong"            => [
        'title' => "Lelong",
        'x'     => 206,
        'y'     => 97,
      ],
      "pierrePetit"       => [
        'title' => "Pierre Petit",
        'x'     => 387,
        'y'     => 114,
      ],
      "laMediatheque"     => [
        'title' => "La Médiathèque",
        'x'     => 106,
        'y'     => 150,
      ],
      "ced"               => [
        'title' => "CED",
        'x'     => 444,
        'y'     => 161,
      ],
      "oratoire"          => [
        'title' => "Oratoire",
        'x'     => 500,
        'y'     => 183,
      ],
      "colombani"         => [
        'title' => "Colombani",
        'x'     => 346,
        'y'     => 197,
      ],
      "laLingerie"        => [
        'title' => "La Lingerie",
        'x'     => 390,
        'y'     => 216,
      ],
      "laChaufferie"      => [
        'title' => "La Chaufferie",
        'x'     => 287,
        'y'     => 216,
      ],
      "robin"             => [
        'title' => "Robin",
        'x'     => 437,
        'y'     => 240,
      ],
      "pasteur"           => [
        'title' => "Pasteur",
        'x'     => 311,
        'y'     => 275,
      ],
      "jalaguier"         => [
        'title' => "Jalaguier",
        'x'     => 433,
        'y'     => 297,
      ],
      "rapine"            => [
        'title' => "Rapine",
        'x'     => 365,
        'y'     => 312,
      ],
    ];

    static $buildingsSimple = [
        "maisonDesMedecins" => "Maison des médecins",
        "lepage" => "Lepage",
        "pinard" => "Pinard",
        "lelong" =>"Lelong",
        "pierrePetit" => "Pierre Petit",
        "laMediatheque" => "La Médiathèque",
        "ced" => "CED",
        "oratoire" => "Oratoire",
        "colombani"=> "Colombani",
        "laLingerie" => "La Lingerie",
        "laChaufferie" => "La Chaufferie",
        "robin" => "Robin",
        "pasteur" => "Pasteur",
        "jalaguier" => "Jalaguier",
        "rapine" => "Rapine",
    ];

    // E-mail configuration
    public static function bodyMail(
      $type,
      User $user,
      $url,
      $randomPassword,
      Organisation $organisation = null
    ) {
        $body = '';
        switch ($type) {
            case GrandsVoisinsConfig::ORGANISATION:
                $body = "Bonjour ".$user->getUsername(
                  )." !<br><br> Votre organisation ".$organisation->getName()." a été créee. <br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur ".$url."<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : ".$user->getUsername()."<br>
                    Mot de passe : ".$randomPassword."<br><br>
                    Cordialement,
                    L'équipe";
                break;
            case GrandsVoisinsConfig::TEAM:
                $body = "Bonjour ".$user->getUsername()." !<br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur ".$url."<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : ".$user->getUsername()."<br>
                    Mot de passe : ".$randomPassword."<br><br>
                    Cordialement,
                    L'équipe";
                break;

        }

        return $body;


    }


}
