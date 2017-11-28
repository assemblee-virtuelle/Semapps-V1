<?php
/**
 * Created by PhpStorm.
 * User: weeger
 * Date: 27/02/2017
 * Time: 15:49
 */

namespace semappsBundle;


class semappsConfig
{
		//class
		const URI_PAIR_PERSON = 'http://virtual-assembly.org/pair#Person';
		const URI_PAIR_ORGANIZATION ='http://virtual-assembly.org/pair#Organization';
		const URI_PAIR_PROJECT ='http://virtual-assembly.org/pair#Project';
		const URI_PAIR_EVENT ='http://virtual-assembly.org/pair#Event';
		const URI_PAIR_PROPOSAL = 'http://virtual-assembly.org/pair#Proposal';
		const URI_PAIR_DOCUMENT = 'http://virtual-assembly.org/pair#Document';
		const URI_PAIR_DOCUMENT_TYPE = 'http://virtual-assembly.org/pair#DocumentType';
		const URI_PAIR_ADDRESS = 'http://virtual-assembly.org/pair#Address';
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

		];
		//thesaurus
		const URI_SKOS_THESAURUS = 'http://www.w3.org/2004/02/skos/core#Concept';

		const Multiple = '';
		const PREFIX = 'urn:mm/contacts/row/';

    static $buildings = [
      "urn:mm/building/grandChateau" => [
        'title' => "Grand chateau",
        'x'     => '65%',
        'y'     => '17%',
      ],
      "urn:mm/building/petitChateau"            => [
        'title' => "Petit chateau",
        'x'     => '42%',
        'y'     => '26%',
      ],
      "urn:mm/building/boisDesCochets"            => [
        'title' => "Cours des cochets",
        'x'     => '26%',
        'y'     => '50%',
      ],
      "urn:mm/building/pigeonnier"            => [
        'title' => "Pigeonnier",
        'x'     => '41%',
        'y'     => '52%',
      ],
      "urn:mm/building/orangerie"       => [
        'title' => "Orangerie",
        'x'     => '54%',
        'y'     => '57%',
      ],
      "urn:mm/building/camping"     => [
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
      "urn:mm/building/grandChateau"  => "Grand Chateau",
      "urn:mm/building/petitChateau"  => "Petit chateau",
      "urn:mm/building/boisDesCochets"=> "Cours des cochets",
      "urn:mm/building/pigeonnier"    => "Pigeonnier",
      "urn:mm/building/orangerie"     => "Orangerie",
      "urn:mm/building/camping"       => "Camping",

    ];

    static $buildingsExtended = [
        "urn:mm/building/grandChateau"  => "Grand Chateau",
        "urn:mm/building/petitChateau"  => "Petit chateau",
        "urn:mm/building/boisDesCochets"=> "Cours des cochets",
        "urn:mm/building/pigeonnier"    => "Pigeonnier",
        "urn:mm/building/orangerie"     => "Orangerie",
        "urn:mm/building/camping"       => "Camping",
        "partout"       => "Partout",
        "exterieur"     => "Extérieurs",
        "ailleurs"      => "Ailleurs",
    ];

}
