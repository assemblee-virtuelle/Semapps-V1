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

        $body = "Salut à toi ".$user->getUsername()." ! <br><br>
                        Nous te souhaitons la bienvenue sur la plateforme des Grands Voisins http://reseau.lesgrandsvoisins.org/ <br><br>
                        
                        Pour valider/accéder à votre profil, merci de vous rendre sur ".$url."<br><br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        Identifiant : ".$user->getUsername()."<br>
                        Mot de passe : ".$randomPassword."
                        <br><br>
                        Quelques remarques à ce sujet :<br>
                        - Tu ne peux te connecter qu’aux personnes disposant d’un profil sur la carto, si tu ne les trouves pas, invite les ;-)<br>
                        - Les centres d’intérêts, les compétences, les offres et besoins de ressources sont gérés avec Wikipedia, pour l’instant on ne peut les saisir qu’en anglais, ce qui devrait évoluer dans les prochains mois. Utilise un traducteur si jamais ! <br>
                        - Si vous souhaitez devenir admin de votre orga, demandez aux personnes identifiées comme responsables ou admin de votre orga sur la carto ;-) Il leur suffira de changer votre rôle dans “Equipe”<br><br>
                       
                        Si tu es référent / administrateur de ton organisation, n’hésite pas à :<br>
                        - Compléter la fiche de ta structure (Menu de gauche “Mon organisation”)<br>
                        - Inviter les membres de ton équipe / ton centre via l’onglet équipe dans l’interface d’administration.<br>
                        - En renseignant des projets que vous développez, des événements que vous organisez, des propositions que vous portez (dans le menu admin de gauche ...) <br><br>
                        
                        Des questions ? Nous y répondons en live sur le channel carto du slack des Grands Voisins, par mail : contact@assemblee-virtuelle.org ou par tel : 06 28 34 54 99<br><br>
                        Merci à vous,<br><br>
                        
                        William, Charlotte, Guillaume, Romain, Jean-Marc, Sébastien, Tristan, Frédéric et toute l’équipe ! (sans oublier Bobby !) <br><br>
                        
                        Cette application 100% open-source a été développée par l’équipe de l’Assemblée Virtuelle (que vous pouvez soutenir !), le projet a reçu 2000 euros de la part des Grands Voisins, les données sont hébergées par Aurore, elles ne sont pas revendues, ni utilisées en dehors de ce projet. L’application se base sur l’utilisation du web sémantique, et va évoluer dans les prochains mois (intégration de nouvelles fonctionnalités, capacité de réplication et de décentralisation de l’application. Objectif : créer un réseau social P2P de la transition). ";



        return $body;
    }


}
