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
    const ORGANISATION = 1;
    const TEAM = 2;

    static $buildings = [
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
    ];

    // E-mail configuration
    public static function bodyMail($type, User $user, $conf_token, $randomPassword, Organisation $organisation = null){
        $body = '';
        switch($type){
            case GrandsVoisinsConfig::ORGANISATION:
                $body = "Bonjour ".$user->getUsername()." !<br><br> Votre organisation ".$organisation->getName()." a été créee. <br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur http://localhost:8000/register/confirm/".$conf_token.".<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : ".$user->getUsername()."<br>
                    Mot de passe : ".$randomPassword."<br><br>
                    Cordialement,
                    L'équipe";
                break;
            case GrandsVoisinsConfig::TEAM:
                $body = "Bonjour ".$user->getUsername()." !<br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur http://localhost:8000/register/confirm/".$conf_token.".<br><br>
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
