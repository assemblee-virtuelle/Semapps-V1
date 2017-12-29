<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 28/11/2017
 * Time: 10:14
 */

namespace semappsBundle\Services;


use semappsBundle\semappsConfig;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
class WebserviceTools
{
    const TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    protected $sfClient;

    /** @var  \Doctrine\ORM\EntityManager */
    protected $em;
    /** @var  \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
    protected $checker;

    /** @var  \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
    protected $tokenStorage;
    /** @var  confManager */
    protected $confmanager;
    public function __construct(EntityManager $em,TokenStorage $tokenStorage,AuthorizationChecker $checker,confManager $confmanager, SemanticFormsClient $sfClient){
        $this->sfClient = $sfClient;
        $this->em = $em;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->confmanager = $confmanager;
    }
    public function searchSparqlRequest($term, $type = semappsConfig::Multiple, $filter=null, $isBlocked = false)
    {
        $this
            ->em
            ->getRepository('semappsBundle:User');
        $arrayType = explode('|',$type);
        $arrayType = array_flip($arrayType);
        $typeOrganization = array_key_exists(semappsConfig::URI_PAIR_ORGANIZATION,$arrayType);
        $typePerson= array_key_exists(semappsConfig::URI_PAIR_PERSON,$arrayType);
        $typeProject= array_key_exists(semappsConfig::URI_PAIR_PROJECT,$arrayType);
        $typeEvent= array_key_exists(semappsConfig::URI_PAIR_EVENT,$arrayType);
        $typeDocument= array_key_exists(semappsConfig::URI_PAIR_DOCUMENT,$arrayType);
        $typeDocumentType= array_key_exists(semappsConfig::URI_PAIR_DOCUMENT_TYPE,$arrayType);
        $typeProjectType= array_key_exists(semappsConfig::URI_PAIR_PROJECT_TYPE,$arrayType);
        $typeEventType= array_key_exists(semappsConfig::URI_PAIR_EVENT_TYPE,$arrayType);
        $typeProposalType= array_key_exists(semappsConfig::URI_PAIR_PROPOSAL_TYPE,$arrayType);
        $typeOrganizationType= array_key_exists(semappsConfig::URI_PAIR_ORGANIZATION_TYPE,$arrayType);
        $typeProposition= array_key_exists(semappsConfig::URI_PAIR_PROPOSAL,$arrayType);
        $typeThesaurus= array_key_exists(semappsConfig::URI_SKOS_THESAURUS,$arrayType);
        //$userLogged =  $this->getUser() != null;
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        /** TODO: move to sparqlRepository */
        /* requete génériques */
        $sparql->addPrefixes($sparql->prefixes)
            ->addPrefix('pair','http://virtual-assembly.org/pair#')
            ->addSelect('?uri')
            ->addSelect('?type')
            ->addSelect('?image')
            ->addSelect('?desc')
            ->addSelect('?address');;
        //->addSelect('?Address');
        ($filter)? $sparql->addWhere('?uri','pair:hasInterest',$sparql->formatValue($filter,$sparql::VALUE_TYPE_URL),'?GR' ) : null;
        //($term != '*')? $sparql->addWhere('?uri','text:query',$sparql->formatValue($term,$sparql::VALUE_TYPE_TEXT),'?GR' ) : null;
        $sparql->addWhere('?uri','rdf:type', '?type','?GR')
            ->addOptional('?uri','pair:adress','?address','?GR')
            ->groupBy('?uri ?type ?title ?image ?desc ?address')
            ->orderBy($sparql::ORDER_ASC,'?title');
        $organizations =[];
        if($type == semappsConfig::Multiple || $typeOrganization ){
            $orgaSparql = clone $sparql;
            $orgaSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_ORGANIZATION,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:image','?image','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //->addOptional('?uri','pair:hostedIn','?building','?GR');
            if($term)$orgaSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?address) , lcase("'.$term.'")) ');
            //dump($orgaSparql->getQuery());
            $results = $this->sfClient->sparql($orgaSparql->getQuery());
            $organizations = $this->sfClient->sparqlResultsValues($results);
        }
        $persons = [];
        if($type == semappsConfig::Multiple || $typePerson ){

            $personSparql = clone $sparql;
            $personSparql->addSelect('?lastName')
                ->addSelect('?firstName')
                ->addSelect('( COALESCE(?lastName, "") As ?result) (fn:concat(?firstName, " " , ?result) as ?title)')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_PERSON,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:firstName','?firstName','?GR')
                ->addOptional('?uri','pair:image','?image','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR')
                ->addOptional('?uri','pair:lastName','?lastName','?GR')
                ->addOptional('?org','rdf:type','pair:Organization','?GR');
            //->addOptional('?org','pair:hostedIn','?building','?GR');
            if($term)$personSparql->addFilter('contains( lcase(?firstName)+ " " + lcase(?lastName), lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?lastName)  , lcase("'.$term.'")) || contains( lcase(?firstName)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $personSparql->groupBy('?firstName ?lastName');
            //dump($personSparql->getQuery());exit;
            $results = $this->sfClient->sparql($personSparql->getQuery());
            $persons = $this->sfClient->sparqlResultsValues($results);

        }
        $projects = [];
        if($type == semappsConfig::Multiple || $typeProject ){
            $projectSparql = clone $sparql;
            $projectSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_PROJECT,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:image','?image','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //->addOptional('?uri','pair:building','?building','?GR');
            if($term)$projectSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?address) , lcase("'.$term.'"))');
            $results = $this->sfClient->sparql($projectSparql->getQuery());
            $projects = $this->sfClient->sparqlResultsValues($results);

        }
        $events = [];
        if(($type == semappsConfig::Multiple || $typeEvent) ){
            $eventSparql = clone $sparql;
            $eventSparql->addSelect('?title')
                ->addSelect('?start')
                ->addSelect('?end')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_EVENT,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:image','?image','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR')
                //->addOptional('?uri','pair:localizedBy','?Address','?GR')
                ->addOptional('?uri','pair:startDate','?start','?GR')
                ->addOptional('?uri','pair:endDate','?end','?GR');
            if($term)$eventSparql->addFilter('contains( lcase(?title), lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?address) , lcase("'.$term.'"))');
            $eventSparql->orderBy($sparql::ORDER_ASC,'?start')
                ->groupBy('?start')
                ->groupBy('?end');
            $results = $this->sfClient->sparql($eventSparql->getQuery());
            $events = $this->sfClient->sparqlResultsValues($results);

        }
        $propositions = [];
        if(($type == semappsConfig::Multiple || $typeProposition) ){
            $propositionSparql = clone $sparql;
            $propositionSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_PROPOSAL,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:image','?image','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$propositionSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$propositionSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($propositionSparql->getQuery());
            $propositions = $this->sfClient->sparqlResultsValues($results);
        }
        $documents = [];
        if((($type == semappsConfig::Multiple || $typeDocument) ) ){
            $documentSparql = clone $sparql;
            $documentSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_DOCUMENT,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?address) , lcase("'.$term.'"))');
            $results = $this->sfClient->sparql($documentSparql->getQuery());
            $documents= $this->sfClient->sparqlResultsValues($results);
        }
        $documentTypes = [];
        if((($type == semappsConfig::Multiple || $typeDocumentType) && !$isBlocked)){
            $documentTypeSparql = clone $sparql;
            $documentTypeSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_DOCUMENT_TYPE,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentTypeSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentTypeSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($documentTypeSparql->getQuery());
            $documentTypes = $this->sfClient->sparqlResultsValues($results);
        }
        $projectTypes = [];
        if((($type == semappsConfig::Multiple || $typeProjectType) && !$isBlocked)){
            $documentTypeSparql = clone $sparql;
            $documentTypeSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_PROJECT_TYPE,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentTypeSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentTypeSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($documentTypeSparql->getQuery());
            $projectTypes = $this->sfClient->sparqlResultsValues($results);
        }
        $eventTypes = [];
        if((($type == semappsConfig::Multiple || $typeEventType) && !$isBlocked)){
            $documentTypeSparql = clone $sparql;
            $documentTypeSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_EVENT_TYPE,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentTypeSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentTypeSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($documentTypeSparql->getQuery());
            $eventTypes = $this->sfClient->sparqlResultsValues($results);
        }
        $proposalTypes = [];
        if((($type == semappsConfig::Multiple || $typeProposalType) && !$isBlocked)){
            $documentTypeSparql = clone $sparql;
            $documentTypeSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_PROPOSAL_TYPE,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentTypeSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentTypeSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($documentTypeSparql->getQuery());
            $proposalTypes = $this->sfClient->sparqlResultsValues($results);
        }
        $organizationTypes = [];
        if((($type == semappsConfig::Multiple || $typeOrganizationType) && !$isBlocked)){
            $documentTypeSparql = clone $sparql;
            $documentTypeSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_PAIR_ORGANIZATION_TYPE,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','pair:preferedLabel','?title','?GR')
                ->addOptional('?uri','pair:comment','?desc','?GR');
            //$documentTypeSparql->addOptional('?uri','pair:building','?building','?GR');
            if($term)$documentTypeSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'"))|| contains( lcase(?address) , lcase("'.$term.'")) ');
            $results = $this->sfClient->sparql($documentTypeSparql->getQuery());
            $organizationTypes = $this->sfClient->sparqlResultsValues($results);
        }
        $thematiques = [];
        if($type == semappsConfig::Multiple || $typeThesaurus ){
            $thematiqueSparql = clone $sparql;
            $thematiqueSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(semappsConfig::URI_SKOS_THESAURUS,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','skos:prefLabel','?title','?GR');
            if($term)$thematiqueSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'"))');
            $results = $this->sfClient->sparql($thematiqueSparql->getQuery());
            $thematiques = $this->sfClient->sparqlResultsValues($results);
        }

        $results = array_merge($organizations,$persons,$projects,$events,$propositions,$thematiques,$documents,$documentTypes,$projectTypes,$eventTypes,$proposalTypes,$organizationTypes);

        return $results;
    }

    public function sparqlGetLabel($url, $uriType)
    {
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($sparql->prefixes)
            ->addPrefix('pair','http://virtual-assembly.org/pair#')
            ->addSelect('?uri')
            ->addFilter('?uri = <'.$url.'>');

        switch ($uriType) {
            case semappsConfig::URI_PAIR_PERSON :
                $sparql->addSelect('( COALESCE(?lastName, "") As ?result)  (fn:concat(?firstName, " ", ?result) as ?label)')
                    ->addWhere('?uri','pair:firstName','?firstName','?gr')
                    ->addOptional('?uri','pair:lastName','?lastName','?gr');

                break;
            case semappsConfig::URI_PAIR_ORGANIZATION :
            case semappsConfig::URI_PAIR_PROJECT :
            case semappsConfig::URI_PAIR_PROPOSAL :
            case semappsConfig::URI_PAIR_EVENT :
            case semappsConfig::URI_PAIR_DOCUMENT :
            case semappsConfig::URI_PAIR_DOCUMENT_TYPE :
            case semappsConfig::URI_PAIR_PROJECT_TYPE :
            case semappsConfig::URI_PAIR_EVENT_TYPE :
            case semappsConfig::URI_PAIR_PROPOSAL_TYPE :
            case semappsConfig::URI_PAIR_ORGANIZATION_TYPE :
                $sparql->addSelect('?label')
                    ->addWhere('?uri','pair:preferedLabel','?label','?gr');

                break;
            case semappsConfig::URI_SKOS_THESAURUS:
                $sparql->addSelect('?label')
                    ->addWhere('?uri','skos:prefLabel','?label','?gr');
                break;
            default:
                $sparql->addSelect('( COALESCE(?firstName, "") As ?result_1)')
                    ->addSelect('( COALESCE(?lastName, "") As ?result_2)')
                    ->addSelect('( COALESCE(?name, "") As ?result_3)')
                    ->addSelect('( COALESCE(?skos, "") As ?result_4)')
                    ->addSelect('(fn:concat(?result_4,?result_3,?result_2, " ", ?result_1) as ?label)')
                    ->addWhere('?uri','rdf:type','?type','?gr')
                    ->addOptional('?uri','pair:firstName','?firstName','?gr')
                    ->addOptional('?uri','pair:lastName','?lastName','?gr')
                    ->addOptional('?uri','pair:preferedLabel','?name','?gr')
                    ->addOptional('?uri','skos:prefLabel','?skos','?gr')
                    ->addOptional('?uri','pair:comment','?desc','?gr')
                    ->addOptional('?uri','pair:image','?image','?gr');
                //->addOptional('?uri','gvoi:building','?building','?gr');
                break;
        }


        // Count buildings.
        //dump($sparql->getQuery());
        $response = $this->sfClient->sparql($sparql->getQuery());
        if (isset($response['results']['bindings'][0]['label']['value'])) {
            return $response['results']['bindings'][0]['label']['value'];
        }

        return false;
    }


    public function requestPair($uri)
    {
        $output     = [];
        $properties = $this->uriPropertiesFiltered($uri);
        $output['uri'] = $uri;
        $dbpediaConf = $this->confmanager->getConf();
        switch (current($properties['type'])) {
            // Orga.
            case  semappsConfig::URI_PAIR_ORGANIZATION:
                // Organization should be saved internally.

                $organization = $this->em->getRepository(
                    'semappsBundle:Organization'
                )->findOneBy(
                    [
                        'sfOrganisation' => $uri,
                    ]
                );
                if(!is_null($organization))
                    $output['id'] = $organization->getId();
                $output['title'] = current($properties['preferedLabel']);
                $propertiesWithUri =[
                    'hasResponsible',
                    'hasMember',
                    'employs',
                    'affiliates',
                    'partnerOf',
                    'involvedIn',
                    'manages',
                    'organizes',
                    'participantOf',
                    'brainstorms',
                    'documentedBy',
                    'subjectOfPAIR',
                    'internal_author',
                    'internal_contributor',
                    'internal_publisher',
                    'hasType',
                ];
                $this->getData($properties,$propertiesWithUri,$output);
                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }

                if (isset($properties['offers'])) {
                    foreach ($properties['offers'] as $uri) {
                        $output['offers'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                if (isset($properties['needs'])) {
                    foreach ($properties['needs'] as $uri) {
                        $output['needs'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                break;
            // Person.
            case  semappsConfig::URI_PAIR_PERSON:
                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }
                $output ['title'] = current($properties['firstName']).' '.current($properties['lastName']);
                $propertiesWithUri = [
                    'knows',
                    'affiliatedTo',
                    'responsibleOf',
                    'memberOf',
                    'employedBy',
                    'involvedIn',
                    'manages',
                    'participantOf',
                    'brainstorms',
                    'organizes',
                    'documentedBy',
                    'subjectOfPAIR',
                    'internal_author',
                    'internal_contributor',
                    'internal_publisher',
                ];
                $this->getData($properties,$propertiesWithUri,$output);
                if (isset($properties['offers'])) {
                    foreach ($properties['offers'] as $uri) {
                        $output['offers'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                if (isset($properties['needs'])) {
                    foreach ($properties['needs'] as $uri) {
                        $output['needs'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                if (isset($properties['skill'])) {
                    foreach ($properties['skill'] as $uri) {
                        $output['skill'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                break;
            // Project.
            case semappsConfig::URI_PAIR_PROJECT:
                $output['title'] = current($properties['preferedLabel']);

                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }

                $propertiesWithUri = [
//									'concretizes',
                    'involves',
                    'managedBy',
//									'representedBy',
                    'documentedBy',
                    'subjectOfPAIR',
                    'hasType',

                ];
                if (isset($properties['needs'])) {
                    foreach ($properties['needs'] as $uri) {
                        $output['needs'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                if (isset($properties['offers'])) {
                    foreach ($properties['offers'] as $uri) {
                        $output['offers'][] = [
                            'uri'  => $uri,
                            'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                        ];
                    }
                }
                $this->getData($properties,$propertiesWithUri,$output);
                break;
            // Event.
            case semappsConfig::URI_PAIR_EVENT:
                $output['title'] = current($properties['preferedLabel']);

                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }
                $propertiesWithUri = [
                    'organizedBy',
                    'hasParticipant',
                    'documentedBy',
                    'subjectOfPAIR',
                    'hasType',
                    'hasSubjectPAIR'

                ];
                $this->getData($properties,$propertiesWithUri,$output);
                break;
            // Proposition.
            case semappsConfig::URI_PAIR_PROPOSAL:
                $output['title'] = current($properties['preferedLabel']);

                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }

                $propertiesWithUri = [
                    'brainstormedBy',
                    'concretizedBy',
                    #'representedBy',
                    'documentedBy',
                    'hasSubjectPAIR',
                    'hasType',

                ];
                $this->getData($properties,$propertiesWithUri,$output);
                break;
            // document
            case semappsConfig::URI_PAIR_DOCUMENT:
                $output['title'] = current($properties['preferedLabel']);

                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }
                $propertiesWithUri = [
                    'documents',
                    'references',
                    'referencesBy',
                    'hasType',
                    'subjectOfPAIR',
                    'internal_document_author',
                    'internal_document_contributor',
                    'internal_document_publisher',
                ];
                $this->getData($properties,$propertiesWithUri,$output);
                break;
            //document type
            case semappsConfig::URI_PAIR_DOCUMENT_TYPE:
            case semappsConfig::URI_PAIR_PROJECT_TYPE:
            case semappsConfig::URI_PAIR_EVENT_TYPE:
            case semappsConfig::URI_PAIR_PROPOSAL_TYPE:
            case semappsConfig::URI_PAIR_ORGANIZATION_TYPE:
                $output['title'] = current($properties['preferedLabel']);

                if (isset($properties['description'])) {
                    $properties['description'] = nl2br(current($properties['description']),false);
                }
                $propertiesWithUri = [
                    'typeOf'
                ];
                //dump($properties);exit;
                $this->getData($properties,$propertiesWithUri,$output);
                break;
        }
        if (isset($properties['hasSubject'])) {
            foreach ($properties['hasSubject'] as $uri) {
                $output['hasSubject'][] = [
                    'uri'  => $uri,
                    'name' => $this->sfClient->dbPediaLabel($dbpediaConf,$uri),
                ];
            }
        }
        if (isset($properties['hasInterest'])) {
            foreach ($properties['hasInterest'] as $uri) {
                $result = [
                    'uri' => $uri,
                    'name' => $this->sparqlGetLabel($uri,semappsConfig::URI_SKOS_THESAURUS)
                ];
                $output['hasInterest'][] = $result;
            }
        }
        $output['properties'] = $properties;

        //dump($output);
        return $output;

    }

    private function getData($properties,$tabFieldsAlias,&$output){
        $cacheTemp = [];
        $cacheComponentConf = [];
        foreach ($tabFieldsAlias as $alias) {
            if (isset($properties[$alias])) {
                foreach (array_unique($properties[$alias]) as $uri) {
                    if (array_key_exists($uri, $cacheTemp)) {
                        $output[$alias][$cacheComponentConf[$cacheTemp[$uri]['type']]['nameType']][] = $cacheTemp[$uri];
                    } else {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        if(array_key_exists('type',$component)){
                            $componentType = current($component['type']);
                            if(array_key_exists($componentType,$cacheComponentConf)){
                                $componentConf = $cacheComponentConf[$componentType];
                            }
                            else
                                $componentConf = $cacheComponentConf[$componentType] = $this->confmanager->getConf($componentType);

                            $result = null;
                            switch ($componentType) {
                                case semappsConfig::URI_PAIR_PERSON:
                                    $result = [
                                        'uri' => $uri,
                                        'name' => ((current($component['firstName'])) ? current($component['firstName']) : "") . " " . ((current($component['lastName'])) ? current($component['lastName']) : ""),
                                        'image' => (!isset($component['image'])) ? '/common/images/no_avatar.png' : $component['image'],
                                    ];
                                    $output[$alias][$componentConf['nameType']][] = $result;
                                    break;
                                case semappsConfig::URI_PAIR_ORGANIZATION:
                                case semappsConfig::URI_PAIR_PROJECT:
                                case semappsConfig::URI_PAIR_EVENT:
                                case semappsConfig::URI_PAIR_PROPOSAL:
                                case semappsConfig::URI_PAIR_DOCUMENT:
                                case semappsConfig::URI_PAIR_DOCUMENT_TYPE:
                                case semappsConfig::URI_PAIR_PROPOSAL_TYPE:
                                case semappsConfig::URI_PAIR_EVENT_TYPE:
                                case semappsConfig::URI_PAIR_PROJECT_TYPE:
                                case semappsConfig::URI_PAIR_ORGANIZATION_TYPE:
                                    $result = [
                                        'uri' => $uri,
                                        'name' => ((current($component['preferedLabel'])) ? current($component['preferedLabel']) : ""),
                                        'image' => (!isset($component['image'])) ? '/common/images/no_avatar.png' : $component['image'],
                                    ];
                                    $output[$alias][$componentConf['nameType']][] = $result;
                                    break;
                            }
                            $cacheTemp[$uri] = $result;
                            $cacheTemp[$uri]['type'] = $componentType;
                        }
                    }
                }
            }
        }
    }

    private function uriPropertiesFiltered($uri)
    {
        $properties   = $this->sfClient->uriProperties($uri);
        $output       = [
            'graph' => implode(',',$properties['graph'])
        ];
        $user         = $this->tokenStorage->getToken()->GetUser();
        $this
            ->em
            ->getRepository('semappsBundle:User')
            ->getAccessLevelString($user);
        if(array_key_exists(self::TYPE,$properties)){
            $sfConf = $this->confmanager->getConf(current($properties[self::TYPE]));
            foreach ($sfConf['fields'] as $field =>$detail){
                if ($detail['access'] === 'anonymous' ||
                    $this->checker->isGranted('ROLE_'.strtoupper($detail['access']))

                ){
                    if (isset($properties[$field])) {
                        $output[$detail['value']] = $properties[$field];
                    }
                }
            }
        }
        return $output;
    }

}