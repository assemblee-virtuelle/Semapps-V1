<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class WebserviceController extends Controller
{
    var $entitiesTabs = [
      SemanticFormsBundle::URI_FOAF_ORGANIZATION => [
        'name'   => 'Organisation',
        'plural' => 'Organisations',
        'icon'   => 'tower',
      ],
      SemanticFormsBundle::URI_FOAF_PERSON       => [
        'name'   => 'Personne',
        'plural' => 'Personnes',
        'icon'   => 'user',
      ],
      SemanticFormsBundle::URI_FOAF_PROJECT      => [
        'name'   => 'Projet',
        'plural' => 'Projets',
        'icon'   => 'screenshot',
      ],
      SemanticFormsBundle::URI_PURL_EVENT        => [
        'name'   => 'Event',
        'plural' => 'Events',
        'icon'   => 'calendar',
      ],
      SemanticFormsBundle::URI_FIPA_PROPOSITION  => [
        'name'   => 'Proposition',
        'plural' => 'Propositions',
        'icon'   => 'info-sign',
      ],
    ];

    var $entitiesFilters = [
      SemanticFormsBundle::URI_FOAF_ORGANIZATION,
      SemanticFormsBundle::URI_FOAF_PERSON,
      SemanticFormsBundle::URI_FOAF_PROJECT,
      SemanticFormsBundle::URI_PURL_EVENT,
      SemanticFormsBundle::URI_FIPA_PROPOSITION,
      SemanticFormsBundle::URI_SKOS_THESAURUS,
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

        if (!$parameters->isHit()) {
            $user = $this->GetUser();

            // Get results.
            $results = $this->searchSparqlRequest(
              '*',
              SemanticFormsBundle::URI_SKOS_THESAURUS
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
              ->getRepository('GrandsVoisinsBundle:User')
              ->getAccessLevelString($user);

            // If no internet, we use a cached version of services
            // placed int face_service folder.
            if ($this->container->hasParameter('no_internet')) {
                $output = ['no_internet' => 1];
            } else {
                $output = [
                  'access'       => $access,
                  'fieldsAccess' => $this->container->getParameter('fields_access'),
                  'buildings'    => $this->getBuildings(),
                  'entities'     => $this->entitiesTabs,
                  'thesaurus'    => $thesaurus,
                ];
            }

            $parameters->set($output);

            $cache->save($parameters);
        }

        return new JsonResponse($parameters->get());
    }

    public function getBuildings()
    {
        $sfClient = $this->container->get('semantic_forms.client');
        // Count buildings.
        $response = $sfClient->sparql(
        // Use common and custom prefixes.
          $sfClient->prefixesCompiled."\n\n ".
          // Query.
          'SELECT ?building ( STR(xsd:integer(COUNT(?building))) AS ?count ) '.
          'WHERE { '.
          '  GRAPH ?GR { '.
          // Retrieve building.
          '    ?uri gvoi:building ?building . '.
          // Only organizations.
          '    ?uri rdf:type <http://xmlns.com/foaf/0.1/Organization> . '.
          '  } '.
          '} '.
          'GROUP BY ?building '.
          'ORDER BY fn:lower-case(?building) '
        );

        $buildings = GrandsVoisinsConfig::$buildings;
        if (is_array($response)) {
            $response = $sfClient->sparqlResultsValues($response);
            foreach ($response as $item) {
                if (isset($buildings[$item['building']])) {
                    $buildings[$item['building']]['organizationCount'] = (int)$item['count'];
                }
            }
        }

        return $buildings;
    }

    public function sparqlSelectType(
      $fieldsRequired,
      $fieldsOptional = [],
      $select,
      $selectType,
      $where = '',
      $thesaurusFilter = null
    ) {
        $requestSelect = '?uri ';
        $requestFields = '';

        foreach ($fieldsRequired as $alias => $type) {
            $requestSelect .= ' ?'.$alias;
            $requestFields .= ' ?uri '.$type.' ?'.$alias.' . ';
        }
        //dump($thesaurusFilter);
        $thesaurusFilter = ($thesaurusFilter)? ' ?uri gvoi:thesaurus <'.$thesaurusFilter.'> . ' : '';
        //dump($thesaurusFilter);
        // Add optional fields.
        foreach ($fieldsOptional as $alias => $type) {
            $requestSelect .= ' ?'.$alias;
            $requestFields .= 'OPTIONAL { ?uri '.$type.' ?'.$alias.' } ';
        }
        $selectType = (!$selectType) ? '' : '  ?uri rdf:type <'.$selectType.'> .';

        return $this->container->get(
          'semantic_forms.client'
        )->prefixesCompiled."\n\n ".
        'SELECT '.$requestSelect.$select.' '.
        'WHERE { '.
        '  GRAPH ?GR { '.
        $selectType.
        $where
        .$requestFields.$thesaurusFilter.
        // Group all duplicated items.
        '}} GROUP BY '.$requestSelect;

    }

    public function searchSparqlSelect(
      $selectType,
      $term,
      $fieldsRequired,
      $fieldsOptional = [],
      $filter = null,
      $select = '',
      $requestSuffix = ''
    ) {
        $request =
          $this->sparqlSelectType(
            $fieldsRequired,
            $fieldsOptional,
            $select,
            $selectType,
            // If not term specified, do not filter term.
            ($term && $term !== '*' ? '    ?uri text:query "'.$term.'" . ' : ''),
            $filter
          );
        /** @var \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient $sfClient */
        $sfClient = $this->container->get('semantic_forms.client');
        $results  = $sfClient->sparql($request . $requestSuffix);
        //dump($request . $requestSuffix);
        // Key values pairs only.
        // Avoid "Empty result" string.
        $results = is_array($results) ? $sfClient->sparqlResultsValues(
          $results
        ) : [];

        // Filter only allowed types.
        $filtered = [];
        foreach ($results as $result) {
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


    public function searchSparqlRequest($term, $type = SemanticFormsBundle::Multiple,$filter=null)
    {
        $arrayType = explode('|',$type);
        $arrayType = array_flip($arrayType);
        $typeOrganization = array_key_exists(SemanticFormsBundle::URI_FOAF_ORGANIZATION,$arrayType);
        $typePerson= array_key_exists(SemanticFormsBundle::URI_FOAF_PERSON,$arrayType);
        $typeProject= array_key_exists(SemanticFormsBundle::URI_FOAF_PROJECT,$arrayType);
        $typeEvent= array_key_exists(SemanticFormsBundle::URI_PURL_EVENT,$arrayType);
        $typeProposition= array_key_exists(SemanticFormsBundle::URI_FIPA_PROPOSITION,$arrayType);
        $typeThesaurus= array_key_exists(SemanticFormsBundle::URI_SKOS_THESAURUS,$arrayType);

        $organizations =
            ($type == SemanticFormsBundle::Multiple || $typeOrganization )? $this->searchSparqlSelect(
        // Type.
          SemanticFormsBundle::URI_FOAF_ORGANIZATION,
          // Search term.
          $term,
          // Required fields.
          [
            'type'  => 'rdf:type',
            'title' => 'foaf:name',
          ],
          // Optional fields..
          [
            'image'    => 'foaf:img',
            'desc'     => 'foaf:status',
            //'subject'  => 'purl:subject',
            'building' => 'gvoi:building',
          ],
          $filter,
                '',
                ' ORDER BY ASC(?title)'

        ): [];
        $persons = ($type == SemanticFormsBundle::Multiple || $typePerson )?
            $this->searchSparqlSelect(
        // Type.
          SemanticFormsBundle::URI_FOAF_PERSON,
          // Search term.
          $term,
          // Required fields.
          [
            'type'       => 'rdf:type',
            'givenName'  => 'foaf:givenName',
          ],
          [
            'familyName' => 'foaf:familyName',
            'image' => 'foaf:img',
            'desc'     => 'foaf:status',
          ],
            $filter,
                '( COALESCE(?familyName, "") As ?result) (fn:concat(?givenName, " " , ?result) as ?title) ',
            ' ORDER BY ASC(?title)'
          ) : [];
        $project       =
          ($type == SemanticFormsBundle::Multiple || $typeProject) ?
            $this->searchSparqlSelect(
            // Type.
              SemanticFormsBundle::URI_FOAF_PROJECT,
              // Search term.
              $term,
              // Required fields.
              [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',

            ],
            // Optional fields..
            [
                'desc'  => 'foaf:status',
                'building' => 'gvoi:building',
            ],
            $filter,
                '',
                ' ORDER BY ASC(?title)'
        ):[];
        $event =
            ($type == SemanticFormsBundle::Multiple || $typeEvent )?
            $this->searchSparqlSelect(
            // Type.
              SemanticFormsBundle::URI_PURL_EVENT,
              // Search term.
              $term,
              // Required fields.
              [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',

            ],
            // Optional fields..
            [
                'desc'  => 'foaf:status',
                'building' => 'gvoi:building',
            ],
             $filter,
                '',
                ' ORDER BY ASC(?title)'
        ):[];
        $proposition =
            ($type == SemanticFormsBundle::Multiple || $typeProposition )?
            $this->searchSparqlSelect(
            // Type.
              SemanticFormsBundle::URI_FIPA_PROPOSITION,
              // Search term.
              $term,
              // Required fields.
              [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',

            ],
            // Optional fields..
            [
                'desc'  => 'foaf:status',
                'building' => 'gvoi:building',
            ],
            $filter,'',
                ' ORDER BY ASC(?title)'
        ):[];
        $thematiques =
            ($type == SemanticFormsBundle::Multiple || $typeThesaurus )?
            $this->searchSparqlSelect(
            // Type.
              SemanticFormsBundle::URI_SKOS_THESAURUS,
              // Search term.
              $term,
              // Required fields.
              [
                'type'  => 'rdf:type',
                'title' => 'skos:prefLabel',
            ],
            // Optional fields..
            [],
            $filter,
            '',
            ' ORDER BY ASC(?title)'
        ):[];
        $results = [];

        while ($organizations || $persons || $project
          || $event || $proposition || $thematiques) {

            if (!empty($organizations)) {
                $results[] = array_shift($organizations);
            }
            if (!empty($persons)) {
                $results[] = array_shift($persons);
            }
            if (!empty($project)) {
                $results[] = array_shift($project);
            }
            if (!empty($event)) {
                $results[] = array_shift($event);
            }
            if (!empty($proposition)) {
                $results[] = array_shift($proposition);
            }
            if (!empty($thematiques)) {
                $results[] = array_shift($thematiques);
            }
        }

        return $results;
    }

    public function searchRequestAction(Request $request)
    {
        // Get term.
        $term = $request->query->get('t');

        // Show request for debug.
        return new Response($this->searchSparqlRequest($term));
    }

    public function searchAction(Request $request)
    {
        // Search
        return new JsonResponse(
          (object)[
            'results' => $this->searchSparqlRequest(
              $request->get('t').'*',
              ''
              ,$request->get('f')
            ),
          ]
        );
    }

    public function lookupAction(Request $request)
    {
        $queryString = $request->get('QueryString');
        $queryClass  = $request->get('QueryClass');
        $sfClient    = $this->container->get('semantic_forms.client');
        $results     = $sfClient->lookup($queryString, $queryClass);

        return new JsonResponse($results);
    }

    public function fieldUriSearchAction(Request $request)
    {
        $output = [];
        // Get results.
        $results = $this->searchSparqlRequest($request->get('QueryString').'*',$request->get('rdfType'));
        // Transform data to match to uri field (uri => title).
        foreach ($results as $item) {
            $output[$item['uri']] = $item['title'];
        }

        return new JsonResponse((object)$output);
    }

    public function sparqlGetLabel($url, $uriType)
    {
        $optionalFields = [];
        $select         = '';

        switch ($uriType) {
            case SemanticFormsBundle::URI_FOAF_PERSON :
                $requiredFields = [
                  'givenName'  => 'foaf:givenName',
                  'familyName' => 'foaf:familyName',
                ];
                $optionalFields = [
                  'desc' => 'foaf:status',
                ];
                // Build a label.
                $select = ' ( COALESCE(?familyName, "") As ?result)  (fn:concat(?givenName, " ", ?result) as ?label) ';
                break;
            case SemanticFormsBundle::URI_FOAF_ORGANIZATION :
                $requiredFields = [
                  'label' => 'foaf:name',
                ];
                $optionalFields = [];
                break;
            case SemanticFormsBundle::URI_FOAF_PROJECT :
            case SemanticFormsBundle::URI_FIPA_PROPOSITION :
            case SemanticFormsBundle::URI_PURL_EVENT :
                $requiredFields = [
                  'label' => 'rdfs:label',
                ];
                $optionalFields = [
                  'desc' => 'foaf:status',
                ];
                break;
            case SemanticFormsBundle::URI_SKOS_THESAURUS:
                $requiredFields = [
                  'label' => 'skos:prefLabel',
                ];
                break;
            default:
                $requiredFields = [
                  'type' => 'rdf:type',
                ];
                $optionalFields = [
                  'givenName'  => 'foaf:givenName',
                  'familyName' => 'foaf:familyName',
                  'name'       => 'foaf:name',
                  'label_test' => 'rdfs:label',
                  'skos'       => 'skos:prefLabel',
                  'desc'       => 'foaf:status',
                ];
                $select         = ' ( COALESCE(?givenName, "") As ?result_1)
                  ( COALESCE(?familyName, "") As ?result_2)
                  ( COALESCE(?name, "") As ?result_3)
                  ( COALESCE(?label_test, "") As ?result_4)
                  ( COALESCE(?skos, "") As ?result_5)
                  (fn:concat(?result_5,?result_4,?result_3,?result_2, " ", ?result_1) as ?label) ';
                break;
        }
        //$select .= ' ORDER BY ASC(?label)';
        $request = $this->sparqlSelectType(
          $requiredFields,
          $optionalFields,
          $select,
          $uriType,
          'FILTER (?uri = <'.$url.'>)'
        );

        $sfClient = $this->container->get('semantic_forms.client');
        // Count buildings.
        $response = $sfClient->sparql($request);
        if (isset($response['results']['bindings'][0]['label']['value'])) {
            return $response['results']['bindings'][0]['label']['value'];
        }

        return false;
    }

    public function fieldUriLabelAction(Request $request)
    {
        $label = $this->sparqlGetLabel(
          $request->get('uri'),
          SemanticFormsBundle::Multiple
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
          ->getRepository('GrandsVoisinsBundle:User')
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
            case  SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                // Organization should be saved internally.
                $organization = $this->getDoctrine()->getRepository(
                  'GrandsVoisinsBundle:Organisation'
                )->findOneBy(
                  [
                    'sfOrganisation' => $uri,
                  ]
                );
                $output['id'] = $organization->getId();
                if (isset($properties['head'])) {
                    foreach ($properties['head'] as $uri) {
                        //dump($person);
                        $output['responsible'][] = $this->getData(
                          $uri,
                          SemanticFormsBundle::URI_FOAF_PERSON
                        );
                    }
                }
                if (isset($properties['hasMember'])) {
                    foreach ($properties['hasMember'] as $uri) {
                        //dump($person);
                        $output['hasMember'][] = $this->getData(
                          $uri,
                          SemanticFormsBundle::URI_FOAF_PERSON
                        );
                    }
                }
                if (isset($properties['OrganizationalCollaboration'])) {
                    foreach ($properties['OrganizationalCollaboration'] as $uri) {
                        //dump($person);
                        $output['OrganizationalCollaboration'][] = $this->getData(
                            $uri,
                            SemanticFormsBundle::URI_FOAF_ORGANIZATION
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
                            case SemanticFormsBundle::URI_FOAF_PROJECT:
                                $projet[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PROJECT
                                );
                                break;
                            case SemanticFormsBundle::URI_PURL_EVENT:
                                $event[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_PURL_EVENT
                                );
                                break;
                            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
                                $proposition[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FIPA_PROPOSITION
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
            case  SemanticFormsBundle::URI_FOAF_PERSON:
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
                          SemanticFormsBundle::URI_FOAF_ORGANIZATION
                        );
                    }
                }
                if (isset($properties['currentProject'])) {
                    foreach ($properties['currentProject'] as $uri) {
                        $output['projects'][] = $this->getData(
                          $uri,
                          SemanticFormsBundle::URI_FOAF_PROJECT
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
                          SemanticFormsBundle::URI_FOAF_PERSON
                        );
                    }
                }
                $projet = $event = $proposition = array();
                if (isset($properties['made'])) {
                    foreach ($properties['made'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])) {
                            case SemanticFormsBundle::URI_FOAF_PROJECT:
                                $projet[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PROJECT
                                );
                                break;
                            case SemanticFormsBundle::URI_PURL_EVENT:
                                $event[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_PURL_EVENT
                                );
                                break;
                            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
                                $proposition[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FIPA_PROPOSITION
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
            case SemanticFormsBundle::URI_FOAF_PROJECT:
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
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PERSON
                                );
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_ORGANIZATION
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
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PERSON
                                );
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_ORGANIZATION
                                );
                                break;
                        }
                    }
                    $output['person_head'] = $person;
                    $output['orga_head']   = $orga;
                }
                break;
            // Event.
            case SemanticFormsBundle::URI_PURL_EVENT:
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
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PERSON
                                );
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_ORGANIZATION
                                );
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker']   = $orga;
                }
                break;
            // Proposition.
            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
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
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_PERSON
                                );
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData(
                                  $uri,
                                  SemanticFormsBundle::URI_FOAF_ORGANIZATION
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
                  SemanticFormsBundle::URI_SKOS_THESAURUS
                );
            }
        }

        $output['properties'] = $properties;

        //dump($output);
        return $output;

    }

    private function getData($uri, $type)
    {

        switch ($type) {
            case SemanticFormsBundle::URI_FOAF_PERSON:
            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
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
            case SemanticFormsBundle::URI_FOAF_PROJECT:
            case SemanticFormsBundle::URI_PURL_EVENT:
            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
            case SemanticFormsBundle::URI_SKOS_THESAURUS:
                return [
                  'uri'  => $uri,
                  'name' => $this->sparqlGetLabel(
                    $uri,
                    $type
                  ),
                ];

        }

        return [];
    }
}
