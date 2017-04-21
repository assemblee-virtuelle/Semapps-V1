<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class WebserviceController extends Controller
{
    var $entitiesParameters = [
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
      SemanticFormsBundle::URI_FOAF_PROJECT       => [
          'name'   => 'Projet',
          'plural' => 'Projets',
          'icon'   => 'screenshot',
      ],
      SemanticFormsBundle::URI_PURL_EVENT       => [
          'name'   => 'Event',
          'plural' => 'Events',
          'icon'   => 'calendar',
      ],
      SemanticFormsBundle::URI_FIPA_PROPOSITION       => [
          'name'   => 'Proposition',
          'plural' => 'Propositions',
          'icon'   => 'info-sign',
      ],
    ];

    public function __construct()
    {
        // We also need to type as property.
        foreach ($this->entitiesParameters as $key => $item) {
            $this->entitiesParameters[$key]['type'] = $key;
        }
    }

    public function parametersAction()
    {
        $user = $this->GetUser();

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
              'entities'     => $this->entitiesParameters,
            ];
        }

        return new JsonResponse($output);
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
      $where = ''
    ) {
        $requestSelect = '?uri ';
        $requestFields = '';

        foreach ($fieldsRequired as $alias => $type) {
            $requestSelect .= ' ?'.$alias;
            $requestFields .= ' ?uri '.$type.' ?'.$alias.' . ';
        }

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
        .$requestFields.
        // Group all duplicated items.
        '}} GROUP BY '.$requestSelect;

    }

    public function searchSparqlSelect(
      $selectType,
      $term,
      $fieldsRequired,
      $fieldsOptional = [],
      $select = ''
    ) {
        $request =
          $this->sparqlSelectType(
            $fieldsRequired,
            $fieldsOptional,
            $select,
            $selectType,
            // If not term specified, do not filter term.
            ($term && $term !== '*' ? '    ?uri text:query "'.$term.'" . ' : '')
          );

        $sfClient = $this->container->get('semantic_forms.client');
        $results  = $sfClient->sparql($request);

        // Key values pairs only.
        // Avoid "Empty result" string.
        $results = is_array($results) ? $sfClient->sparqlResultsValues(
          $results
        ) : [];

        // Filter only allowed types.
        $filtered = [];
        foreach ($results as $result) {
            // Type is sometime missing.
            if (isset($result['type']) && isset($this->entitiesParameters[$result['type']])) {
                $filtered[] = $result;
            }
        }

        return $filtered;
    }


    public function searchSparqlRequest($term)
    {

        $organizations = $this->searchSparqlSelect(
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
          ]
        );

        $persons = $this->searchSparqlSelect(
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
            '( COALESCE(?familyName, "") As ?result) (fn:concat(?givenName, " " , ?result) as ?title) '
        );
        $project = $this->searchSparqlSelect(
        // Type.
            SemanticFormsBundle::URI_FOAF_PROJECT,
            // Search term.
            $term,
            // Required fields.
            [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',
                'desc'  => 'foaf:status',
            ],
            // Optional fields..
            []
        );
        $event = $this->searchSparqlSelect(
        // Type.
            SemanticFormsBundle::URI_PURL_EVENT,
            // Search term.
            $term,
            // Required fields.
            [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',
                'desc'  => 'foaf:status',
            ],
            // Optional fields..
            []
        );
        $proposition = $this->searchSparqlSelect(
        // Type.
            SemanticFormsBundle::URI_FIPA_PROPOSITION,
            // Search term.
            $term,
            // Required fields.
            [
                'type'  => 'rdf:type',
                'title' => 'rdfs:label',
                'desc'  => 'foaf:status',
            ],
            // Optional fields..
            []
        );
        //dump($persons);
        $results = [];

        while ($organizations || $persons) {
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
              $request->get('t').'*'
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
        $results = $this->searchSparqlRequest($request->get('QueryString').'*');
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
                $optionalFields =[
                    'desc'  => 'foaf:status',
                ];
                // Build a label.
                $select = ' ( COALESCE(?familyName, "") As ?result)  (fn:concat(?givenName, " ", ?result) as ?label) ';
                break;
            case SemanticFormsBundle::URI_FOAF_ORGANIZATION :
                $requiredFields = [
                  'label' => 'foaf:name',
                ];
                $optionalFields =[];
                break;
            case SemanticFormsBundle::URI_FOAF_PROJECT :
            case SemanticFormsBundle::URI_FIPA_PROPOSITION :
            case SemanticFormsBundle::URI_PURL_EVENT :
                $requiredFields = [
                  'label' => 'rdfs:label',
                ];
                $optionalFields =[
                    'desc'  => 'foaf:status',
                ];
                break;
            default:
                $requiredFields =[
                    'type' => 'rdf:type'
                ];
                $optionalFields =[
                    'givenName'  => 'foaf:givenName',
                    'familyName' => 'foaf:familyName',
                    'name' => 'foaf:name',
                    'label_test' => 'rdfs:label',
                    'desc'  => 'foaf:status',
                ];
                $select = ' ( COALESCE(?givenName, "") As ?result_1)
                  ( COALESCE(?familyName, "") As ?result_2)
                  ( COALESCE(?name, "") As ?result_3)
                  ( COALESCE(?label_test, "") As ?result_4)
                  (fn:concat(?result_4,?result_3,?result_2, " ", ?result_1) as ?label) ';
                break;
        }

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
                    $output['responsible'] = $this->uriPropertiesFiltered(
                      current($properties['head'])
                    );
                }
                if (isset($properties['hasMember'])) {
                    foreach ($properties['hasMember'] as $uri) {
                        $person = $this->uriPropertiesFiltered($uri);
                        //dump($person);
                        $output['hasMember'][] = [
                            'uri'   => $uri,
                            'name'  => $this->sparqlGetLabel(
                                $uri,
                                SemanticFormsBundle::URI_FOAF_PERSON
                            ),
                            'image' => (!isset($person['image']))? '/common/images/no_avatar.jpg' : $person['image'],
                        ];
                    }
                }
                $projet = $event = $proposition = array();
                if (isset($properties['made'])) {
                    foreach ($properties['made'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PROJECT:
                                $projet[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PROJECT);
                                break;
                            case SemanticFormsBundle::URI_PURL_EVENT:
                                $event[] = $this->getData($uri,SemanticFormsBundle::URI_PURL_EVENT);
                                break;
                            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
                                $proposition[] = $this->getData($uri,SemanticFormsBundle::URI_FIPA_PROPOSITION);
                                break;
                        }
                    }
                    $output['projet'] = $projet;
                    $output['event'] = $event;
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
                        $person = $this->uriPropertiesFiltered($uri);
                        //dump($person);
                        $output['memberOf'][] = [
                            'uri'   => $uri,
                            'name'  => $this->sparqlGetLabel(
                                $uri,
                                SemanticFormsBundle::URI_FOAF_ORGANIZATION
                            ),
                            'image' => (!isset($person['image']))? '/common/images/no_avatar.jpg' : $person['image'],
                        ];
                    }
                }
                if (isset($properties['currentProject'])) {
                    foreach ($properties['currentProject'] as $uri) {
                        $output['projects'][] = [
                          'uri'  => $uri,
                          'name' => $this->sparqlGetLabel(
                            $uri,
                            SemanticFormsBundle::URI_FOAF_PROJECT
                          ),
                        ];
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
                        $person = $this->uriPropertiesFiltered($uri);
                        //dump($person);
                        $output['knows'][] = [
                          'uri'   => $uri,
                          'name'  => $this->sparqlGetLabel(
                            $uri,
                            SemanticFormsBundle::URI_FOAF_PERSON
                          ),
                          'image' => (!isset($person['image']))? '/common/images/no_avatar.jpg' : $person['image'],
                        ];
                    }
                }
                $projet = $event = $proposition = array();
                if (isset($properties['made'])) {
                    foreach ($properties['made'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PROJECT:
                                $projet[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PROJECT);
                                break;
                            case SemanticFormsBundle::URI_PURL_EVENT:
                                $event[] = $this->getData($uri,SemanticFormsBundle::URI_PURL_EVENT);
                                break;
                            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
                                $proposition[] = $this->getData($uri,SemanticFormsBundle::URI_FIPA_PROPOSITION);
                                break;
                        }
                    }
                    $output['projet'] = $projet;
                    $output['event'] = $event;
                    $output['proposition'] = $proposition;
                }

                if (isset($properties['headOf'])) {
                    $output['responsible'] = $this->uriPropertiesFiltered(
                        current($properties['headOf'])
                    );
                }
                break;
            case SemanticFormsBundle::URI_FOAF_PROJECT:
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                        '/^mailto:/',
                        '',
                        current($properties['mbox'])
                    );
                }
                $person = $orga  = array();
                if (isset($properties['maker'])) {
                    foreach ($properties['maker'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PERSON);
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_ORGANIZATION);
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker'] = $orga;
                }
                $person = $orga =array();
                if (isset($properties['fundedBy'])) {
                    foreach ($properties['fundedBy'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PERSON);
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_ORGANIZATION);
                                break;
                        }
                    }
                    $output['person_fundedBy'] = $person;
                    $output['orga_fundedBy'] = $orga;
                }
                break;
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
                $person = $orga  = array();
                if (isset($properties['maker'])) {
                    foreach ($properties['maker'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PERSON);
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_ORGANIZATION);
                                break;
                        }
                    }
                    $output['person_maker'] = $person;
                    $output['orga_maker'] = $orga;
                }
                break;
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
                $person = $orga =array();
                if (isset($properties['fundedBy'])) {
                    foreach ($properties['fundedBy'] as $uri) {
                        $component = $this->uriPropertiesFiltered($uri);
                        //dump($component);
                        switch (current($component['type'])){
                            case SemanticFormsBundle::URI_FOAF_PERSON:
                                $person[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_PERSON);
                                break;
                            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
                                $orga[] = $this->getData($uri,SemanticFormsBundle::URI_FOAF_ORGANIZATION);
                                break;
                        }
                    }
                    $output['person_fundedBy'] = $person;
                    $output['orga_fundedBy'] = $orga;
                }
                break;
        }

        $output['properties'] = $properties;
        //dump($output);
        return $output;

    }

    private function getData($uri,$type){

        switch ($type) {
            case SemanticFormsBundle::URI_FOAF_PERSON:
            case SemanticFormsBundle::URI_FOAF_ORGANIZATION:
            $temp= $this->uriPropertiesFiltered($uri);
            return [
                'uri'   => $uri,
                'name'  => $this->sparqlGetLabel(
                    $uri,
                    $type
                ),
                'image' => (!isset($temp['image']))? '/common/images/no_avatar.jpg' : $temp['image'],
            ];
                break;
            case SemanticFormsBundle::URI_FOAF_PROJECT:
            case SemanticFormsBundle::URI_PURL_EVENT:
            case SemanticFormsBundle::URI_FIPA_PROPOSITION:
            return [
                'uri'   => $uri,
                'name'  => $this->sparqlGetLabel(
                    $uri,
                    $type
                ),
            ];

        }
        return [];
    }
}
