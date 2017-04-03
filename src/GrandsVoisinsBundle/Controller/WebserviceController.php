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

        return $this->container->get(
          'semantic_forms.client'
        )->prefixesCompiled."\n\n ".
        'SELECT '.$requestSelect.$select.' '.
        'WHERE { '.
        '  GRAPH ?GR { '.
        '  ?uri rdf:type <'.$selectType.'> .'.
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
            'subject'  => 'purl:subject',
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
            'familyName' => 'foaf:familyName',
          ],
          [
            'image' => 'foaf:img',
          ],
          // Group names into title.
          ' (fn:concat(?givenName, " ", ?familyName) as ?title) '
        );

        $results = [];

        while ($organizations || $persons) {
            if (!empty($organizations)) {
                $results[] = array_shift($organizations);
            }
            if (!empty($persons)) {
                $results[] = array_shift($persons);
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
        $requiredFields = [];
        $select         = '';

        switch ($uriType) {
            case SemanticFormsBundle::URI_FOAF_PERSON :
                $requiredFields = [
                  'givenName'  => 'foaf:givenName',
                  'familyName' => 'foaf:familyName',
                ];
                // Build a label.
                $select = ' (fn:concat(?givenName, " ", ?familyName) as ?label) ';
                break;
            case SemanticFormsBundle::URI_FOAF_ORGANIZATION :
                $requiredFields = [
                  'label' => 'foaf:name',
                ];
                break;
            case SemanticFormsBundle::URI_FOAF_PROJECT :
                $requiredFields = [
                  'label' => 'rdfs:label',
                ];

                break;
        }

        $request = $this->sparqlSelectType(
          $requiredFields,
          [],
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
        // TODO Various types.
        $label = $this->sparqlGetLabel(
          $request->get('uri'),
          SemanticFormsBundle::URI_FOAF_PERSON
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
                if (isset($properties['hasResponsible'])) {
                    $output['responsible'] = $this->uriPropertiesFiltered(
                      $properties['hasResponsible']
                    );
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
                    $output['organisation'] = [
                      'uri'  => current($properties['memberOf']),
                      'name' => $this->sparqlGetLabel(
                        current($properties['memberOf']),
                        SemanticFormsBundle::URI_FOAF_ORGANIZATION
                      ),
                    ];
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
                        $image  = $this->getLocalImageFromUri($uri);
                        //dump($person);
                        $output['knows'][] = [
                          'uri'   => $uri,
                          'name'  => $this->sparqlGetLabel(
                            $uri,
                            SemanticFormsBundle::URI_FOAF_PERSON
                          ),
                          'image' => !$image && isset($person['image'][0]) ? $person['image'][0] : $image,
                        ];
                    }
                }
                break;
        }

        $output['properties'] = $properties;

        return $output;

    }

    public function getLocalImageFromUri($uri)
    {
        $user = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:User')
          ->findOneBy(
            [
              'sfLink' => $uri,
            ]
          );

        if ($user) {
            $pictureName = $user->getPictureName();
            if ($pictureName) {
                return '/upload/pictures/'.$user->getPictureName();
            }
        }

        return '/common/images/no_avatar.jpg';
    }
}
