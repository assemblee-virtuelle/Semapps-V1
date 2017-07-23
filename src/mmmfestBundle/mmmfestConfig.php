<?php
/**
 * Created by PhpStorm.
 * User: weeger
 * Date: 27/02/2017
 * Time: 15:49
 */

namespace mmmfestBundle;


use mmmfestBundle\Entity\Organisation;
use mmmfestBundle\Entity\User;

class mmmfestConfig
{
    const URI_FOAF_PERSON = 'http://xmlns.com/foaf/0.1/Person';
    const URI_FOAF_ORGANIZATION = 'http://xmlns.com/foaf/0.1/Organization';
    const URI_FOAF_PROJECT = 'http://xmlns.com/foaf/0.1/Project';
    const URI_PURL_EVENT = 'http://purl.org/NET/c4dm/event.owl#Event';
    const URI_FIPA_PROPOSITION = 'http://www.fipa.org/schemas#Proposition';
    const URI_SKOS_THESAURUS = 'http://www.w3.org/2004/02/skos/core#Concept';
    const URI_MIXTE_PERSON_ORGANIZATION = [
      'http://xmlns.com/foaf/0.1/Person',
      'http://xmlns.com/foaf/0.1/Organization'
    ];
    const Multiple = '';

    const REVERSE = [
      mmmfestConfig::URI_FOAF_ORGANIZATION =>[// person => orga
        'http://www.w3.org/ns/org#hasMember' => 'http://www.w3.org/ns/org#memberOf',
      ],
      mmmfestConfig::URI_FOAF_PROJECT => [
        'http://www.w3.org/ns/org#Head' => 'http://xmlns.com/foaf/0.1/made',
        'http://xmlns.com/foaf/0.1/maker' => 'http://xmlns.com/foaf/0.1/made',
      ],
      mmmfestConfig::URI_FIPA_PROPOSITION => [
        'http://xmlns.com/foaf/0.1/maker' => 'http://xmlns.com/foaf/0.1/made',
      ],
      mmmfestConfig::URI_PURL_EVENT => [
        'http://xmlns.com/foaf/0.1/maker' => 'http://xmlns.com/foaf/0.1/made',
      ],
    ];
    CONST SPEC_PERSON = 'form-Person';
    CONST SPEC_ORGANIZATION = 'form-Organization';
    CONST SPEC_PROJECT = 'form-Project';
    CONST SPEC_EVENT = 'form-Event';
    CONST SPEC_PROPOSITION = 'form-Proposition';
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
      "grandChateau" => [
        'title' => "Grand chateau",
        'x'     => '60%',
        'y'     => '21%',
      ],
      "petitChateau"            => [
        'title' => "Petit chateau",
        'x'     => '40%',
        'y'     => '29%',
      ],
      "boisDesCochets"            => [
        'title' => "Bois des cochets",
        'x'     => '22%',
        'y'     => '50%',
      ],
      "pigeonnier"            => [
        'title' => "Pigeonnier",
        'x'     => '39%',
        'y'     => '54%',
      ],
      "orangerie"       => [
        'title' => "Orangerie",
        'x'     => '50%',
        'y'     => '59%',
      ],
      "camping"     => [
        'title' => "Camping",
        'x'     => '0%',
        'y'     => '0%',
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
      "grandChateau"  => "Grand Chateau",
      "petitChateau"  => "Petit chateau",
      "boisDesCochets"=> "Bois des cochets",
      "pigeonnier"    => "Pigeonnier",
      "orangerie"     => "Orangerie",
      "camping"       => "Camping",

    ];

    static $buildingsExtended = [
        "grandChateau"  => "Grand Chateau",
        "petitChateau"  => "Petit chateau",
        "boisDesCochets"=> "Bois des cochets",
        "pigeonnier"    => "Pigeonnier",
        "orangerie"     => "Orangerie",
        "camping"       => "Camping",
        "partout"       => "Partout",
        "exterieur"     => "Extérieurs",
        "ailleurs"      => "Ailleurs",
    ];




}
