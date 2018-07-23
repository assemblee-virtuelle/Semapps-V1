<?php
/**
 * Created by PhpStorm.
 * User: weeger
 * Date: 27/02/2017
 * Time: 15:49
 */

namespace semappsBundle;

/** TODO remove this file */
class coreConfig
{
    //class
    const URI_PAIR_PERSON = 'http://virtual-assembly.org/pair#Person';
    const URI_PAIR_ORGANIZATION ='http://virtual-assembly.org/pair#Organization';
    const URI_PAIR_PROJECT ='http://virtual-assembly.org/pair#Project';
    const URI_PAIR_EVENT ='http://virtual-assembly.org/pair#Event';
    const URI_PAIR_PROPOSAL = 'http://virtual-assembly.org/pair#Proposal';
    const URI_PAIR_DOCUMENT = 'http://virtual-assembly.org/pair#Document';
    const URI_SKOS_CONCEPT = 'http://www.w3.org/2004/02/skos/core#Concept';
    const URI_PAIR_GOOD = 'http://virtual-assembly.org/pair#Good';
    const URI_PAIR_SERVICE = 'http://virtual-assembly.org/pair#Service';
    const URI_PAIR_PLACE = 'http://virtual-assembly.org/pair#Place';
    const URI_MIXTE_PERSON_ORGANIZATION = [
        self::URI_PAIR_PERSON,
        self::URI_PAIR_ORGANIZATION,
    ];
    const URI_ALL_PAIR_EXCEPT_DOC_TYPE = [
        self::URI_PAIR_PERSON,
        self::URI_PAIR_ORGANIZATION,
        self::URI_PAIR_PROJECT,
        self::URI_PAIR_EVENT,
        self::URI_PAIR_PROPOSAL,
        self::URI_PAIR_DOCUMENT,
        self::URI_PAIR_GOOD,
        self::URI_PAIR_SERVICE,
        self::URI_PAIR_PLACE
    ];
    //thesaurus
    const URI_SKOS_THESAURUS = 'http://www.w3.org/2004/02/skos/core#Concept';

    const Multiple = '';
//    const PREFIX = 'urn:semapps/contacts/row/';
//    static $buildings = [
//        "maisonDesMedecins" => [
//            'title' => "Maison des médecins",
//            'x'     => '43%',
//            'y'     => '12%',
//        ],
//        "lepage"            => [
//            'title' => "Lepage",
//            'x'     => '19%',
//            'y'     => '25%',
//        ],
//        "pinard"            => [
//            'title' => "Pinard",
//            'x'     => '53%',
//            'y'     => '22%',
//        ],
//        "lelong"            => [
//            'title' => "Lelong",
//            'x'     => '34%',
//            'y'     => '31%',
//        ],
//        "pierrePetit"       => [
//            'title' => "Pierre Petit",
//            'x'     => '61%',
//            'y'     => '36%',
//        ],
//        "laMediatheque"     => [
//            'title' => "La Médiathèque",
//            'x'     => '18%',
//            'y'     => '45%',
//        ],
//        "ced"               => [
//            'title' => "CED",
//            'x'     => '70%',
//            'y'     => '48%',
//        ],
//        "oratoire"          => [
//            'title' => "Oratoire",
//            'x'     => '79%',
//            'y'     => '53%',
//        ],
//        "colombani"         => [
//            'title' => "Colombani",
//            'x'     => '55%',
//            'y'     => '57%',
//        ],
//        "laLingerie"        => [
//            'title' => "La Lingerie",
//            'x'     => '62%',
//            'y'     => '61%',
//        ],
//        "laChaufferie"      => [
//            'title' => "La Chaufferie",
//            'x'     => '46%',
//            'y'     => '61%',
//        ],
//        "robin"             => [
//            'title' => "Robin",
//            'x'     => '69%',
//            'y'     => '68%',
//        ],
//        "pasteur"           => [
//            'title' => "Pasteur",
//            'x'     => '50%',
//            'y'     => '76%',
//        ],
//        "jalaguier"         => [
//            'title' => "Jalaguier",
//            'x'     => '68%',
//            'y'     => '82%',
//        ],
//        "rapine"            => [
//            'title' => "Rapine",
//            'x'     => '58%',
//            'y'     => '86%',
//        ],
//        "partout"            => [
//            'title' => "Partout",
//        ],
//        "exterieur"            => [
//            'title' => "Exterieurs",
//        ],
//        "ailleurs"            => [
//            'title' => "Ailleurs",
//        ],
//    ];
//    static $buildingsSimple = [
//        "maisonDesMedecins" => "Maison des médecins",
//        "lepage"            => "Lepage",
//        "pinard"            => "Pinard",
//        "lelong"            => "Lelong",
//        "pierrePetit"       => "Pierre Petit",
//        "laMediatheque"     => "La Médiathèque",
//        "ced"               => "CED",
//        "oratoire"          => "Oratoire",
//        "colombani"         => "Colombani",
//        "laLingerie"        => "La Lingerie",
//        "laChaufferie"      => "La Chaufferie",
//        "robin"             => "Robin",
//        "pasteur"           => "Pasteur",
//        "jalaguier"         => "Jalaguier",
//        "rapine"            => "Rapine",
//    ];
}
