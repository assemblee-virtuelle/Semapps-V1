<?php

namespace VirtualAssembly\SemanticFormsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SemanticFormsBundle extends Bundle
{
    const URI_FOAF_PERSON = 'http://xmlns.com/foaf/0.1/Person';
    const URI_FOAF_ORGANIZATION = 'http://xmlns.com/foaf/0.1/Organization';
    const URI_FOAF_PROJECT = 'http://xmlns.com/foaf/0.1/Project';
    const URI_PURL_EVENT = 'http://purl.org/NET/c4dm/event.owl#Event';
    const URI_FIPA_PROPOSITION = 'http://www.fipa.org/schemas#Proposition';

    const REVERSE = [
        SemanticFormsBundle::URI_FOAF_PERSON =>[// person => orga
            'http://www.w3.org/ns/org#memberOf' => 'http://www.w3.org/ns/org#hasMember',
        ],
        SemanticFormsBundle::URI_FOAF_PROJECT => [
            'http://xmlns.com/foaf/0.1/fundedBy' => 'http://xmlns.com/foaf/0.1/made',
            'http://xmlns.com/foaf/0.1/maker' => 'http://xmlns.com/foaf/0.1/made',
        ],
    ];

}
