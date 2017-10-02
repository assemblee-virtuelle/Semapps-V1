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
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents'
			],
			mmmfestConfig::URI_PAIR_PERSON =>[
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#knows' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#knows',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#memberOf' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasMember',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involvedIn' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involves',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#participantOf' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasParticipant'
			],
			mmmfestConfig::URI_PAIR_PROJECT => [
					#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#concretizes' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#concretizedBy',
					'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#managedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#manages' 	,
					'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involves' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involvedIn',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents'
			],
			mmmfestConfig::URI_PAIR_EVENT => [
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizes',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasParticipant' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#participantOf',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents'
			],
			mmmfestConfig::URI_PAIR_PROPOSAL => [
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstormedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstorms' ,
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents'
			],
			mmmfestConfig::URI_PAIR_DOCUMENT => [
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#references' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#referencesBy',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasType' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#typeOf',
				'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents' => 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy'
			],
		];
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
