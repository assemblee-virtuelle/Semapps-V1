<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebserviceController extends Controller
{
    var $entitiesParameters = [
      'http://xmlns.com/foaf/0.1/Organization' => [
        'name'   => 'Organisation',
        'plural' => 'Organisations',
        'icon'   => 'tower',
      ],
      'http://xmlns.com/foaf/0.1/Person'       => [
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

    public function searchSparqlSelect(
      $selectType,
      $term,
      $fieldsRequired,
      $fieldsOptional = [],
      $select = ''
    ) {
        $sfClient      = $this->container->get('semantic_forms.client');
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

        $request = $this->container->get(
            'semantic_forms.client'
          )->prefixesCompiled.
          "\n\n ".
          'SELECT '.$requestSelect.$select.' '.
          'WHERE { '.
          '  GRAPH ?GR { '.
          '  ?uri rdf:type <'.$selectType.'> .'.
          // If not term specified, do not filter term.
          ($term && $term !== '*' ? '    ?uri text:query "'.$term.'" . ' : '').
          // Requested fields.
          $requestFields.
          // Group all duplicated items.
          '}} GROUP BY '.$requestSelect;

        $results = $sfClient->sparql($request);

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
          'http://xmlns.com/foaf/0.1/Organization',
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
          'http://xmlns.com/foaf/0.1/Person',
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
        $results = $this->searchSparqlRequest($request->query->get('t').'*');

        return new JsonResponse((object)['results' => $results]);
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
        $access       = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:User')
          ->getAccessLevelString($user);

//        print_r($properties);exit;

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

        $properties = $this->uriPropertiesFiltered($uri);

        switch ($properties['type']) {
            // Orga.
            case 'http://xmlns.com/foaf/0.1/Organization':
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
            case 'http://xmlns.com/foaf/0.1/Person':
                // Remove mailto: from email.
                if (isset($properties['mbox'])) {
                    $properties['mbox'] = preg_replace(
                      '/^mailto:/',
                      '',
                      $properties['mbox']
                    );
                }
                if (isset($properties['phone'])) {
                    // Remove tel: from phone
                    $properties['phone'] = preg_replace(
                      '/^tel:/',
                      '',
                      $properties['phone']
                    );
                }
                break;
        }

        $output['properties'] = $properties;

        return $output;

    }
}
