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
    const FIRST = 0;
    const ORGANISATION = 1;
    const TEAM = 2;


    // TODO Rename $fieldsAliasesOrganization
    // TODO Voir si il ne faut pas intervertir clefs / valeurs.
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
      'volunteeringProposals' => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#volunteeringProposals',
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
      "partout"            => [
          'title' => "Partout",
      ],
      "exterieur"            => [
          'title' => "Exterieurs",
      ],
      "ailleurs"            => [
          'title' => "Ailleurs",
      ],
    ];

    static $buildingsSimple = [
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

    static $buildingsExtended = [
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
        "partout"           => "Partout",
        "exterieur"         => "Extérieurs",
        "ailleurs"         => "Ailleurs",
    ];

    // E-mail configuration
    public static function bodyMail(
      User $user,
      $url,
      $randomPassword
    ) {
        $body = "Salut Ô Voisin(e)s !<br><br>
                        Vous en avez peut-être entendu parler, depuis plusieurs mois, les associations Aurore et Assemblée Virtuelle travaillent au développement d’un outil de cartographie de la dynamique des Grands Voisins.<br><br>
                        Cet outil est désormais disponible en version publique ! http://reseau.lesgrandsvoisins.org/ Faites y un tour pour voir comment elle marche, et amusez vous à naviguer de bâtiments en organisations en thématiques, … (Recherchez Assemblée Virtuelle par exemple)<br><br>
                        Nous avons pour l’instant intégré les 250 organisations présentes sur le site ainsi que leurs référents, dont vous faites partie puisque vous recevez ce mail.<br><br>
                        Aujourd’hui, nous vous proposons de vous associer à ce projet : <br>
                        - En complétant la fiche de votre organisation ou de votre centre<br>
                        - En invitant les membres de votre équipe / résidents de votre centre à renseigner leur profil sur la carto<br>
                        - En renseignant des projets que vous développez, des événements que vous organisez, des propositions que vous portez (dans le menu admin de gauche ...) 
                        <br><br>
                        Pour accéder à votre profil, merci de vous rendre sur ".$url."<br><br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        Identifiant : ".$user->getUsername()."<br>
                        Mot de passe : ".$randomPassword."
                        <br><br>
                        Projetons nous dans un mois, nous avons tous joué le jeu en prenant 15 minutes pour décrire notre organisation, pour inviter collaborateurs et résidents à prendre part à la carto. 2000 personnes et 500 projets sont désormais référencés, nous avons accès à leur description, à leur numéro de téléphone (accès restreint aux membres), à leurs centres d’intérêts, leurs compétences, nous savons qui fait quoi, qui s’intéresse à quoi. Un besoin ? Une recherche sur la carto, un coup de fil, un café à la lingerie, et biim… une collaboration !<br><br>
                        
                        Elle est pas belle la vie aux Grands Voisins ?<br><br>
                        
                        Des questions ? Nous y répondons en live sur le channel carto du slack des Grands Voisins, par mail : contact@assemblee-virtuelle.org ou par tel : 06 28 34 54 99<br><br>
                        Merci à vous,<br><br>
                        
                        William, Charlotte, Guillaume, Romain, Jean-Marc, Sébastien, Tristan, Frédéric et toute l’équipe ! (sans oublier Bobby !) <br><br>
                        
                        NB. Cette application 100% open-source a été développée par l’équipe de l’Assemblée Virtuelle, le projet a reçu 2000 euros de la part des Grands Voisins, les données sont hébergées par Aurore, elles ne sont pas revendues, ni utilisées en dehors de ce projet. L’application se base sur l’utilisation du web sémantique, et va évoluer dans les prochains mois (intégration de nouvelles fonctionnalités, capacité de réplication et de décentralisation de l’application. Objectif : créer un réseau social P2P de la transition).";
        return $body;
    }


}
