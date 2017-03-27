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

    static $organisationFields = [
        "type"                  => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
        "img"                   => 'http://xmlns.com/foaf/0.1/img',
        "batiment"              => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#building',
        "nom"                   => 'http://xmlns.com/foaf/0.1/name',
        "nomAdministratif"      => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#administrativeName',
        "membres"               => 'http://www.w3.org/ns/org#hasMember',
        "description"           => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#description',
        'topic_interest'        => 'http://xmlns.com/foaf/0.1/topic_interest',
        'conventionType'        => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#conventionType',
        'headOf'                => 'http://www.w3.org/ns/org#headOf',
        'employeesCount'        => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#employeesCount',
        'homepage'              => 'http://xmlns.com/foaf/0.1/homepage',
        'mbox'                  => 'http://xmlns.com/foaf/0.1/mbox',
        'depiction'             => 'http://xmlns.com/foaf/0.1/depiction',
        'room'                  => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#room',
        'arrivalDate'           => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#arrivalDate',
        'status'                => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#status',
        'proposedContribution'  => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#proposedContribution',
        'realisedContribution'  => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#realisedContribution',
        'phone'                 => 'http://xmlns.com/foaf/0.1/phone',
        'twitter'               => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#twitter',
        'linkedin'              => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#linkedin',
        'facebook'              => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#facebook',
        'volunteeringProposals' => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#volunteeringProposals'
    ];

    static $adminFields = [
        "nom"            => 'http://xmlns.com/foaf/0.1/familyName',
        "prenom"         => 'http://xmlns.com/foaf/0.1/givenName',
        "type"           => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
        "img"            => 'http://xmlns.com/foaf/0.1/img',
        "memberOf"       => 'http://www.w3.org/ns/org#memberOf',
        'homepage'       => 'http://xmlns.com/foaf/0.1/homepage',
        'mbox'           => 'http://xmlns.com/foaf/0.1/mbox',
        'phone'          => 'http://xmlns.com/foaf/0.1/phone',
        'currentProject' => 'http://xmlns.com/foaf/0.1/currentProject',
        'topicInterest'  => 'http://xmlns.com/foaf/0.1/topic_interest',
        'knows'          => 'http://xmlns.com/foaf/0.1/knows',
        'expertise'      => 'http://purl.org/ontology/cco/core#expertise',
        'slack'          => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#slack',
        'birthday'       => 'http://xmlns.com/foaf/0.1/birthday',
    ];

    static $buildings = [
      "maisonDesMedecins" => [
        'title' => "Maison des médecins",
        'x'     => '43%',
        'y'     => '12%',
      ],
      "lepage"            => [
        'title' => "Lepage",
        'x'     => '19%',
        'y'     => '25%',
      ],
      "pinard"            => [
        'title' => "Pinard",
        'x'     => '53%',
        'y'     => '22%',
      ],
      "lelong"            => [
        'title' => "Lelong",
        'x'     => '34%',
        'y'     => '31%',
      ],
      "pierrePetit"       => [
        'title' => "Pierre Petit",
        'x'     => '61%',
        'y'     => '36%',
      ],
      "laMediatheque"     => [
        'title' => "La Médiathèque",
        'x'     => '18%',
        'y'     => '45%',
      ],
      "ced"               => [
        'title' => "CED",
        'x'     => '70%',
        'y'     => '48%',
      ],
      "oratoire"          => [
        'title' => "Oratoire",
        'x'     => '79%',
        'y'     => '53%',
      ],
      "colombani"         => [
        'title' => "Colombani",
        'x'     => '55%',
        'y'     => '57%',
      ],
      "laLingerie"        => [
        'title' => "La Lingerie",
        'x'     => '62%',
        'y'     => '61%',
      ],
      "laChaufferie"      => [
        'title' => "La Chaufferie",
        'x'     => '46%',
        'y'     => '61%',
      ],
      "robin"             => [
        'title' => "Robin",
        'x'     => '69%',
        'y'     => '68%',
      ],
      "pasteur"           => [
        'title' => "Pasteur",
        'x'     => '50%',
        'y'     => '76%',
      ],
      "jalaguier"         => [
        'title' => "Jalaguier",
        'x'     => '68%',
        'y'     => '82%',
      ],
      "rapine"            => [
        'title' => "Rapine",
        'x'     => '58%',
        'y'     => '86%',
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
