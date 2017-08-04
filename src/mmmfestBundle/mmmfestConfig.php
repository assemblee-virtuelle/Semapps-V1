<?php
/**
 * Created by PhpStorm.
 * User: weeger
 * Date: 27/02/2017
 * Time: 15:49
 */

namespace mmmfestBundle;


class mmmfestConfig
{
		//class
		const URI_PAIR_PERSON = 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Person';
		const URI_PAIR_ORGANIZATION ='http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Organization';
		const URI_PAIR_PROJECT ='http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Project';
		const URI_PAIR_EVENT ='http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Event';
		const URI_PAIR_PROPOSAL = 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Proposal';
		const URI_PAIR_DOCUMENT = 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Document';
		const URI_PAIR_DOCUMENT_TYPE = 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#DocumentType';
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
		//spec
		CONST SPEC_PERSON = 'form-Person';
		CONST SPEC_ORGANIZATION = 'form-Organization';
		CONST SPEC_PROJECT = 'form-Project';
		CONST SPEC_EVENT = 'form-Event';
		CONST SPEC_PROPOSAL = 'form-Proposal';
		CONST SPEC_DOCUMENT = 'form-Document';
		CONST SPEC_DOCUMENTTYPE = 'form-DocumentType';


		const Multiple = '';
		const PREFIX = 'urn:mm/contacts/row/';
		const REVERSE = [
			mmmfestConfig::URI_PAIR_ORGANIZATION =>[
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasMember' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#memberOf',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasResponsible' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#responsibleOf',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#employs' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#employedBy',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#partnerOf' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#partnerOf',
			],
			mmmfestConfig::URI_PAIR_PERSON =>[
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#knows' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#knows',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#affiliatedTo' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#affiliates'
			],
			mmmfestConfig::URI_PAIR_PROJECT => [
					'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#concretizes' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#concretizedBy',
					'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#managedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#manages' 	,
					'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involves' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involvedIn'
			],
			mmmfestConfig::URI_PAIR_EVENT => [
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizes',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasParticipant' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#participantOf',
			],
			mmmfestConfig::URI_PAIR_PROPOSAL => [
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstormedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstorms' ,
			],

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
