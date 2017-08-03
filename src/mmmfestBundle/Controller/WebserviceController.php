<?php

namespace mmmfestBundle\Controller;

use VirtualAssembly\SparqlBundle\Services\SparqlClient;
use mmmfestBundle\mmmfestConfig;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

class WebserviceController extends Controller
{
    var $entitiesTabs = [
      mmmfestConfig::URI_PAIR_ORGANIZATION => [
        'name'   => 'Organisation',
        'plural' => 'Organisations',
        'icon'   => 'tower',
      ],
      mmmfestConfig::URI_PAIR_PERSON       => [
        'name'   => 'Personne',
        'plural' => 'Personnes',
        'icon'   => 'user',
      ],
      mmmfestConfig::URI_PAIR_PROJECT      => [
        'name'   => 'Projet',
        'plural' => 'Projets',
        'icon'   => 'screenshot',
      ],
      mmmfestConfig::URI_PAIR_EVENT        => [
        'name'   => 'Event',
        'plural' => 'Events',
        'icon'   => 'calendar',
      ],
      mmmfestConfig::URI_PAIR_PROPOSAL  => [
        'name'   => 'Proposition',
        'plural' => 'Propositions',
        'icon'   => 'info-sign',
      ],
    ];

    var $entitiesFilters = [
      mmmfestConfig::URI_PAIR_ORGANIZATION,
      mmmfestConfig::URI_PAIR_PERSON,
      mmmfestConfig::URI_PAIR_PROJECT,
      mmmfestConfig::URI_PAIR_EVENT,
      mmmfestConfig::URI_PAIR_PROPOSAL,
      mmmfestConfig::URI_SKOS_THESAURUS,
    ];

    public function __construct()
    {
        // We also need to type as property.
        foreach ($this->entitiesTabs as $key => $item) {
            $this->entitiesTabs[$key]['type'] = $key;
        }
    }

    public function parametersAction()
    {
        $cache = new FilesystemAdapter();
        $parameters = $cache->getItem('gv.webservice.parameters');

        //if (!$parameters->isHit()) {
            $user = $this->GetUser();

            // Get results.
            $results = $this->searchSparqlRequest(
              '',
              mmmfestConfig::URI_SKOS_THESAURUS
            );

            $thesaurus = [];
            foreach ($results as $item) {
                $thesaurus[] = [
                  'uri'   => $item['uri'],
                  'label' => $item['title'],
                ];
            }

            $access = $this
              ->getDoctrine()
              ->getManager()
              ->getRepository('mmmfestBundle:User')
              ->getAccessLevelString($user);

            $name = ($user != null)? $user->getUsername() : '';
            // If no internet, we use a cached version of services
            // placed int face_service folder.
            if ($this->container->hasParameter('no_internet')) {
                $output = ['no_internet' => 1];
            } else {
                $output = [
                  'access'       => $access,
                  'name'         => $name,
                  'fieldsAccess' => $this->container->getParameter('fields_access'),
                  'buildings'    => mmmfestConfig::$buildings,
                  'entities'     => $this->entitiesTabs,
                  'thesaurus'    => $thesaurus,
                ];
            }

            $parameters->set($output);

            $cache->save($parameters);
        //}

        return new JsonResponse($parameters->get());
    }

    public function searchSparqlRequest($term, $type = mmmfestConfig::Multiple, $filter=null)
    {
        $sfClient    = $this->container->get('semantic_forms.client');
        $arrayType = explode('|',$type);
        $arrayType = array_flip($arrayType);
        $typeOrganization = array_key_exists(mmmfestConfig::URI_PAIR_ORGANIZATION,$arrayType);
        $typePerson= array_key_exists(mmmfestConfig::URI_PAIR_PERSON,$arrayType);
        $typeProject= array_key_exists(mmmfestConfig::URI_PAIR_PROJECT,$arrayType);
        $typeEvent= array_key_exists(mmmfestConfig::URI_PAIR_EVENT,$arrayType);
        $typeProposition= array_key_exists(mmmfestConfig::URI_PAIR_PROPOSAL,$arrayType);
        $typeThesaurus= array_key_exists(mmmfestConfig::URI_SKOS_THESAURUS,$arrayType);
        $userLogged =  $this->getUser() != null;
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        /* requete génériques */
        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
            ->addSelect('?uri')
            ->addSelect('?type')
            ->addSelect('?image')
            ->addSelect('?desc')
            ->addSelect('?building');
        ($filter)? $sparql->addWhere('?uri','gvoi:thesaurus',$sparql->formatValue($filter,$sparql::VALUE_TYPE_URL),'?GR' ) : null;
        //($term != '*')? $sparql->addWhere('?uri','text:query',$sparql->formatValue($term,$sparql::VALUE_TYPE_TEXT),'?GR' ) : null;
        $sparql->addWhere('?uri','rdf:type', '?type','?GR')
            ->groupBy('?uri ?type ?title ?image ?desc ?building')
            ->orderBy($sparql::ORDER_ASC,'?title');
        $organizations =[];
        if($type == mmmfestConfig::Multiple || $typeOrganization ){
            $orgaSparql = clone $sparql;
            $orgaSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_PAIR_ORGANIZATION,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','default:preferedLabel','?title','?GR')
                ->addOptional('?uri','default:representedBy','?image','?GR')
                ->addOptional('?uri','default:comment','?desc','?GR');
                //->addOptional('?uri','gvoi:building','?building','?GR');
            if($term)$orgaSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) ');
            //dump($orgaSparql->getQuery());
            $results = $sfClient->sparql($orgaSparql->getQuery());
            $organizations = $sfClient->sparqlResultsValues($results);
        }
        $persons = [];
        if($type == mmmfestConfig::Multiple || $typePerson ){

            $personSparql = clone $sparql;
            $personSparql->addSelect('?lastName')
                ->addSelect('?firstName')
                ->addSelect('( COALESCE(?lastName, "") As ?result) (fn:concat(?firstName, " " , ?result) as ?title)')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_PAIR_PERSON,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','default:firstName','?firstName','?GR')
                ->addOptional('?uri','default:representedBy','?image','?GR')
                ->addOptional('?uri','default:description','?desc','?GR')
                //->addOptional('?uri','default:building','?building','?GR')
                ->addOptional('?uri','default:lastName','?lastName','?GR');
                //->addOptional('?org','rdf:type','default:Organization','?GR')
                //->addOptional('?org','gvoi:building','?building','?GR');
            if($term)$personSparql->addFilter('contains( lcase(?firstName)+ " " + lcase(?lastName), lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) || contains( lcase(?lastName)  , lcase("'.$term.'")) || contains( lcase(?firstName)  , lcase("'.$term.'")) ');
            $personSparql->groupBy('?firstName ?lastName');
            //dump($personSparql->getQuery());
            $results = $sfClient->sparql($personSparql->getQuery());
            $persons = $sfClient->sparqlResultsValues($results);

        }
        $projects = [];
        if($type == mmmfestConfig::Multiple || $typeProject ){
            $projectSparql = clone $sparql;
            $projectSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_PAIR_PROJECT,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','default:preferedLabel','?title','?GR')
                ->addOptional('?uri','default:representedBy','?image','?GR')
                ->addOptional('?uri','default:comment','?desc','?GR');
                //->addOptional('?uri','default:building','?building','?GR');
            if($term)$projectSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) ');
            $results = $sfClient->sparql($projectSparql->getQuery());
            $projects = $sfClient->sparqlResultsValues($results);

        }
        $events = [];
        if(($type == mmmfestConfig::Multiple || $typeEvent) && $userLogged){
            $eventSparql = clone $sparql;
            $eventSparql->addSelect('?title')
                ->addSelect('?start')
                ->addSelect('?end')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_PAIR_EVENT,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','default:preferedLabel','?title','?GR')
                ->addOptional('?uri','default:representedBy','?image','?GR')
                ->addOptional('?uri','default:comment','?desc','?GR')
                //->addOptional('?uri','default:building','?building','?GR')
                ->addOptional('?uri','default:startDate','?start','?GR')
                ->addOptional('?uri','default:endDate','?end','?GR');
            if($term)$eventSparql->addFilter('contains( lcase(?title), lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) ');
            $eventSparql->orderBy($sparql::ORDER_DESC,'?start')
                ->groupBy('?start')
                ->groupBy('?end');
            $results = $sfClient->sparql($eventSparql->getQuery());
            $events = $sfClient->sparqlResultsValues($results);

        }
        $propositions = [];
        if(($type == mmmfestConfig::Multiple || $typeProposition)&& $userLogged ){
            $propositionSparql = clone $sparql;
            $propositionSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_PAIR_PROPOSAL,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','default:preferedLabel','?title','?GR')
                ->addOptional('?uri','default:representedBy','?image','?GR')
                ->addOptional('?uri','default:comment','?desc','?GR');
            //$propositionSparql->addOptional('?uri','default:building','?building','?GR');
            if($term)$propositionSparql->addFilter('contains( lcase(?title)  , lcase("'.$term.'")) || contains( lcase(?desc)  , lcase("'.$term.'")) ');
            $results = $sfClient->sparql($propositionSparql->getQuery());
            $propositions = $sfClient->sparqlResultsValues($results);
        }

        $thematiques = [];
        if($type == mmmfestConfig::Multiple || $typeThesaurus ){
            $thematiqueSparql = clone $sparql;
            $thematiqueSparql->addSelect('?title')
                ->addWhere('?uri','rdf:type', $sparql->formatValue(mmmfestConfig::URI_SKOS_THESAURUS,$sparql::VALUE_TYPE_URL),'?GR')
                ->addWhere('?uri','skos:prefLabel','?title','?GR');
            if($term)$thematiqueSparql->addFilter('contains( lcase(?title) , lcase("'.$term.'"))');
            $results = $sfClient->sparql($thematiqueSparql->getQuery());
            $thematiques = $sfClient->sparqlResultsValues($results);
        }

        $results = [];

        while ($organizations || $persons || $projects
          || $events || $propositions || $thematiques) {

            if (!empty($organizations)) {
                $results[] = array_shift($organizations);
            }
            if (!empty($persons)) {
                $results[] = array_shift($persons);
            }
            if (!empty($projects)) {
                $results[] = array_shift($projects);
            }
            if (!empty($events)) {
                $results[] = array_shift($events);
            }
            if (!empty($propositions)) {
                $results[] = array_shift($propositions);
            }
            if (!empty($thematiques)) {
                $results[] = array_shift($thematiques);
            }
        }

        return $results;
    }

    public function searchAction(Request $request)
    {
        // Search
        return new JsonResponse(
          (object)[
            'results' => $this->searchSparqlRequest(
              $request->get('t'),
              ''
              ,$request->get('f')
            ),
          ]
        );
    }

    public function fieldUriSearchAction(Request $request)
    {
        $output = [];
        // Get results.
        $results = $this->searchSparqlRequest($request->get('QueryString'),$request->get('rdfType'));
        // Transform data to match to uri field (uri => title).
        foreach ($results as $item) {
            $output[$item['uri']] = $item['title'];
        }

        return new JsonResponse((object)$output);
    }

    public function sparqlGetLabel($url, $uriType)
    {
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
            ->addSelect('?uri')
            ->addFilter('?uri = <'.$url.'>');

        switch ($uriType) {
            case mmmfestConfig::URI_PAIR_PERSON :
                $sparql->addSelect('( COALESCE(?lastName, "") As ?result)  (fn:concat(?firstName, " ", ?result) as ?label)')
                    ->addWhere('?uri','default:firstName','?firstName','?gr')
                    ->addOptional('?uri','default:lastName','?lastName','?gr');

                break;
            case mmmfestConfig::URI_PAIR_ORGANIZATION :
            case mmmfestConfig::URI_PAIR_PROJECT :
            case mmmfestConfig::URI_PAIR_PROPOSAL :
            case mmmfestConfig::URI_PAIR_EVENT :
                $sparql->addSelect('?label')
                    ->addWhere('?uri','default:preferedLabel','?label','?gr');

                break;
            case mmmfestConfig::URI_SKOS_THESAURUS:
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
                    ->addOptional('?uri','default:firstName','?firstName','?gr')
                    ->addOptional('?uri','default:lastName','?lastName','?gr')
                    ->addOptional('?uri','default:preferedLabel','?name','?gr')
                    ->addOptional('?uri','skos:prefLabel','?skos','?gr')
                    ->addOptional('?uri','default:comment','?desc','?gr')
                    ->addOptional('?uri','default:representedBy','?image','?gr');
                    //->addOptional('?uri','gvoi:building','?building','?gr');
                break;
        }


        $sfClient = $this->container->get('semantic_forms.client');
        // Count buildings.
        //dump($sparql->getQuery());
        $response = $sfClient->sparql($sparql->getQuery());
        if (isset($response['results']['bindings'][0]['label']['value'])) {
            return $response['results']['bindings'][0]['label']['value'];
        }

        return false;
    }

    public function fieldUriLabelAction(Request $request)
    {
        $label = $this->sparqlGetLabel(
          $request->get('uri'),
          mmmfestConfig::Multiple
        );

        return new JsonResponse(
          (object)['label' => $label]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function detailAction(Request $request)
    {
        return new JsonResponse(
          (object)[
            'detail' => $this->requestPair($request->get('uri')),
          ]
        );
    }

    public function ressourceAction(Request $request){
        $uri                = $request->get('uri');
        $sfClient           = $this->container->get('semantic_forms.client');
        $nameRessource      = $sfClient->dbPediaLabel($uri);
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
            ->addSelect('?type')
            ->addSelect('?uri')
					->addSelect('( COALESCE(?firstName, "") As ?result_1)')
					->addSelect('( COALESCE(?lastName, "") As ?result_2)')
					->addSelect('( COALESCE(?name, "") As ?result_3)')
					->addSelect('( COALESCE(?skos, "") As ?result_4)')
					->addSelect('(fn:concat(?result_4,?result_3,?result_2, " ", ?result_1) as ?label)')
					->addWhere('?uri','rdf:type','?type','?gr')
					->addOptional('?uri','default:firstName','?firstName','?gr')
					->addOptional('?uri','default:lastName','?lastName','?gr')
					->addOptional('?uri','default:preferedLabel','?name','?gr')
					->addOptional('?uri','skos:prefLabel','?skos','?gr')
					->addOptional('?uri','default:comment','?desc','?gr')
					->addOptional('?uri','default:representedBy','?image','?gr');
        $ressourcesNeeded = clone $sparql;
        $ressourcesNeeded->addWhere('?uri','default:needs',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');

        $requests['ressourcesNeeded'] = $ressourcesNeeded->getQuery();
        $ressourcesProposed = clone $sparql;
        $ressourcesProposed->addWhere('?uri','default:offers',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');
        $requests['ressourcesProposed'] =$ressourcesProposed->getQuery();


        $filtered['name'] = $nameRessource;
        $filtered['uri'] = $uri;
        foreach ($requests as $key => $request){
            //dump($request);
            $results[$key]  = $sfClient->sparql($request);
            $results[$key] = is_array($results[$key]) ? $sfClient->sparqlResultsValues(
                $results[$key]
            ) : [];
            $filtered[$key] = $this->filter($results[$key]);
        }
        return new JsonResponse(
            (object)[
                'ressource' => $filtered,
            ]
        );
    }

    public function uriPropertiesFiltered($uri)
    {
        $sfClient     = $this->container->get('semantic_forms.client');
        $properties   = $sfClient->uriProperties($uri);
        $output       = [];
        $fieldsFilter = $this->container->getParameter('fields_access');
        $user         = $this->GetUser();
        $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:User')
          ->getAccessLevelString($user);
        foreach ($fieldsFilter as $role => $fields) {
            // User has role.
            if ($role === 'anonymous' ||
              $this->isGranted('ROLE_'.strtoupper($role))
            ) {
                foreach ($fields as $fieldName) {
                    // Field should exist.
                    if (isset($properties[$fieldName])) {
                        $output[$fieldName] = $properties[$fieldName];
                    }
                }
            }
        }

        return $output;
    }

    public function requestPair($uri)
    {
        $output     = [];
        $properties = $this->uriPropertiesFiltered($uri);
        $sfClient   = $this->container->get('semantic_forms.client');
        switch (current($properties['type'])) {
            // Orga.
            case  mmmfestConfig::URI_PAIR_ORGANIZATION:
                // Organization should be saved internally.
                $organization = $this->getDoctrine()->getRepository(
                  'mmmfestBundle:Organisation'
                )->findOneBy(
                  [
                    'sfOrganisation' => $uri,
                  ]
                );
                if(!is_null($organization))
                    $output['id'] = $organization->getId();
                if (isset($properties['head'])) {
                    foreach ($properties['head'] as $uri) {
                        //dump($person);
                        $output['responsible'][] = $this->getData(
                          $uri,
                          mmmfestConfig::URI_PAIR_PERSON
                        );
                    }
                }
                $person = $orga = array();
                if (isset($properties['hasMember'])) {
                    foreach ($properties['hasMember'] as $uri) {
                        //dump($person);
                        $component = $this->uriPropertiesFiltered($uri);

                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['person_hasMember'] = $person;
                    $output['orga_hasMember']   = $orga;
                }
                if (isset($properties['memberOf'])) {
                    foreach ($properties['memberOf'] as $uri) {
                        //dump($person);
                        $output['memberOf'][] = $this->getData(
                          $uri,
                          mmmfestConfig::URI_MIXTE_PERSON_ORGANIZATION
                        );
                    }
                }
                if (isset($properties['OrganizationalCollaboration'])) {
                    foreach ($properties['OrganizationalCollaboration'] as $uri) {
                        //dump($person);
                        $output['OrganizationalCollaboration'][] = $this->getData(
                            $uri,
                            mmmfestConfig::URI_PAIR_ORGANIZATION
                        );
                    }
                }
                if (isset($properties['topicInterest'])) {
                    foreach ($properties['topicInterest'] as $uri) {
                        $output['topicInterest'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                $projet = $event = $proposition = array();
                if (isset($properties['made'])) {
                    foreach ($properties['made'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PROJECT:
                                $projet[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_EVENT:
                                $event[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_PROPOSAL:
                                $proposition[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['projet']      = $projet;
                    $output['event']       = $event;
                    $output['proposition'] = $proposition;
                }
                break;
            // Person.
            case  mmmfestConfig::URI_PAIR_PERSON:

                $query = " SELECT ?b WHERE { GRAPH ?G {<".$uri."> rdf:type default:Person . ?org rdf:type default:Organization . ?org gvoi:building ?b .} }";
                //dump($query);
                $buildingsResult = $sfClient->sparql($sfClient->prefixesCompiled . $query);
                $output['building'] = (isset($buildingsResult["results"]["bindings"][0])) ? $buildingsResult["results"]["bindings"][0]['b']['value'] : '';
                // Remove mailto: from email.
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                      '/^mailto:/',
                      '',
                      current($properties['mbox'])
                    );
                }
                if (isset($properties['phone'])) {
                    // Remove tel: from phone
                    $properties['phone'] = preg_replace(
                      '/^tel:/',
                      '',
                      current($properties['phone'])
                    );
                }
                if (isset($properties['memberOf'])) {
                    foreach ($properties['memberOf'] as $uri) {
                        //dump($person);
                        $output['memberOf'][] = $this->getData(
                          $uri,
                          mmmfestConfig::URI_PAIR_ORGANIZATION
                        );
                    }
                }
                if (isset($properties['currentProject'])) {
                    foreach ($properties['currentProject'] as $uri) {
                        $output['projects'][] = $this->getData(
                          $uri,
                          mmmfestConfig::URI_PAIR_PROJECT
                        );
                    }
                }
                if (isset($properties['topicInterest'])) {
                    foreach ($properties['topicInterest'] as $uri) {
                        $output['topicInterest'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                if (isset($properties['city'])) {
                    foreach ($properties['city'] as $uri) {
                        $output['city'] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                if (isset($properties['expertize'])) {
                    foreach ($properties['expertize'] as $uri) {
                        $output['expertize'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                if (isset($properties['knows'])) {
                    foreach ($properties['knows'] as $uri) {
                        //dump($person);
                        $output['knows'][] = $this->getData(
                          $uri,
                          mmmfestConfig::URI_PAIR_PERSON
                        );
                    }
                }
                $projet = $event = $proposition = array();
                if (isset($properties['made'])) {
                    foreach ($properties['made'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PROJECT:
                                $projet[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_EVENT:
                                $event[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_PROPOSAL:
                                $proposition[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['projet']      = $projet;
                    $output['event']       = $event;
                    $output['proposition'] = $proposition;
                }

                if (isset($properties['headOf'])) {
                    $output['responsible'] = $this->uriPropertiesFiltered(
                      current($properties['headOf'])
                    );
                }
                break;
            // Project.
            case mmmfestConfig::URI_PAIR_PROJECT:
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                      '/^mailto:/',
                      '',
                      current($properties['mbox'])
                    );
                }
                $person = $orga = array();
                if (isset($properties['maker'])) {
                    foreach ($properties['maker'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker']   = $orga;
                }
                $person = $orga = array();
                if (isset($properties['head'])) {
                    foreach ($properties['head'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['person_head'] = $person;
                    $output['orga_head']   = $orga;
                }
                if (isset($properties['topicInterest'])) {
                    foreach ($properties['topicInterest'] as $uri) {
                        $output['topicInterest'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                break;
            // Event.
            case mmmfestConfig::URI_PAIR_EVENT:
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                      '/^mailto:/',
                      '',
                      current($properties['mbox'])
                    );
                }
                if (isset($properties['topicInterest'])) {
                    foreach ($properties['topicInterest'] as $uri) {
                        $output['topicInterest'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                $person = $orga = array();
                if (isset($properties['maker'])) {
                    foreach ($properties['maker'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker']   = $orga;
                }
                break;
            // Proposition.
            case mmmfestConfig::URI_PAIR_PROPOSAL:
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                      '/^mailto:/',
                      '',
                      current($properties['mbox'])
                    );
                }
                if (isset($properties['topicInterest'])) {
                    foreach ($properties['topicInterest'] as $uri) {
                        $output['topicInterest'][] = [
                          'uri'  => $uri,
                          'name' => $sfClient->dbPediaLabel($uri),
                        ];
                    }
                }
                $person = $orga = array();
                if (isset($properties['maker'])) {
                    foreach ($properties['maker'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case mmmfestConfig::URI_PAIR_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  current($component['type'])
                                );
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker']   = $orga;
                }
                break;
        }
        if (isset($properties['resourceProposed'])) {
            foreach ($properties['resourceProposed'] as $uri) {
                $output['resourceProposed'][] = [
                  'uri'  => $uri,
                  'name' => $sfClient->dbPediaLabel($uri),
                ];
            }
        }
        if (isset($properties['resourceNeeded'])) {
            foreach ($properties['resourceNeeded'] as $uri) {
                $output['resourceNeeded'][] = [
                  'uri'  => $uri,
                  'name' => $sfClient->dbPediaLabel($uri),
                ];
            }
        }
        if (isset($properties['thesaurus'])) {
            foreach ($properties['thesaurus'] as $uri) {
                $output['thesaurus'][] = $this->getData(
                  $uri,
                  mmmfestConfig::URI_SKOS_THESAURUS
                );
            }
        }
        if (isset($properties['description'])) {
            $properties['description'] = nl2br(current($properties['description']),false);
        }
        $output['properties'] = $properties;

        //dump($output);
        return $output;

    }

    private function getData($uri, $type =null)
    {

        switch ($type) {
            case mmmfestConfig::URI_PAIR_PERSON:
            case mmmfestConfig::URI_PAIR_ORGANIZATION:
                $temp = $this->uriPropertiesFiltered($uri);

                return [
                  'uri'   => $uri,
                  'name'  => $this->sparqlGetLabel(
                    $uri,
                    $type
                  ),
                  'image' => (!isset($temp['image'])) ? '/common/images/no_avatar.jpg' : $temp['image'],
                ];
                break;
            case mmmfestConfig::URI_PAIR_PROJECT:
            case mmmfestConfig::URI_PAIR_EVENT:
            case mmmfestConfig::URI_PAIR_PROPOSAL:
            case mmmfestConfig::URI_SKOS_THESAURUS:
                return [
                  'uri'  => $uri,
                  'name' => $this->sparqlGetLabel(
                    $uri,
                    $type
                  ),
                ];
                break;
            default:
                $temp = $this->uriPropertiesFiltered($uri);
                return [
                  'uri'   => $uri,
                  'name'  => $this->sparqlGetLabel(
                    $uri,
                    $type
                  ),
                  'image' => (!isset($temp['image'])) ? '/common/images/no_avatar.jpg' : $temp['image'],
                ];
                break;
        }
    }

    /**
     * Filter only allowed types.
     * @param array $array
     * @return array
     */
    public function filter(Array $array){
        $filtered = [];
        foreach ($array as $result) {
            // Type is sometime missing.
            if (isset($result['type']) && in_array(
                $result['type'],
                $this->entitiesFilters
              )
            ) {
                $filtered[] = $result;
            }
        }

        return $filtered;
    }
}
