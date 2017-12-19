<?php

namespace semappsBundle\Controller;

use semappsBundle\Entity\User;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;
use semappsBundle\semappsConfig;
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
      semappsConfig::URI_PAIR_ORGANIZATION => [
        'name'   => 'Organisation',
        'plural' => 'Organisations',
        'icon'   => 'tower',
				'nameType' => 'organization'
      ],
      semappsConfig::URI_PAIR_PERSON       => [
        'name'   => 'Personne',
        'plural' => 'Personnes',
        'icon'   => 'user',
				'nameType' => 'person'
      ],
      semappsConfig::URI_PAIR_PROJECT      => [
        'name'   => 'Projet',
        'plural' => 'Projets',
        'icon'   => 'screenshot',
				'nameType' => 'projet'
      ],
      semappsConfig::URI_PAIR_EVENT        => [
        'name'   => 'Event',
        'plural' => 'Events',
        'icon'   => 'calendar',
				'nameType' => 'event'
      ],
      semappsConfig::URI_PAIR_PROPOSAL  => [
        'name'   => 'Proposition',
        'plural' => 'Propositions',
        'icon'   => 'info-sign',
				'nameType' => 'proposition'
      ],
			semappsConfig::URI_PAIR_DOCUMENT  => [
				'name'   => 'Document',
				'plural' => 'Documents',
				'icon'   => 'folder-open',
				'nameType' => 'document'
			],
			semappsConfig::URI_PAIR_DOCUMENT_TYPE  => [
				'name'   => 'Type de document',
				'plural' => 'Types de document',
				'icon'   => 'pushpin',
				'nameType' => 'documenttype'
			],
    ];

    var $entitiesFilters = [
      semappsConfig::URI_PAIR_ORGANIZATION,
      semappsConfig::URI_PAIR_PERSON,
      semappsConfig::URI_PAIR_PROJECT,
      semappsConfig::URI_PAIR_EVENT,
      semappsConfig::URI_PAIR_PROPOSAL,
      semappsConfig::URI_SKOS_THESAURUS,
      semappsConfig::URI_PAIR_DOCUMENT,
      semappsConfig::URI_PAIR_DOCUMENT_TYPE,

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
				$webserviceTools       = $this->get('semappsBundle.webserviceTools');

				//if (!$parameters->isHit()) {
						/** @var User $user */
            $user = $this->GetUser();
            // Get results.
            $results = $webserviceTools->searchSparqlRequest(
              '',
              semappsConfig::URI_SKOS_THESAURUS
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
              ->getRepository('semappsBundle:User')
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
                  'entities'     => $this->entitiesTabs,
                  'thesaurus'    => $thesaurus,
									'userUri'					 => $user->getSfLink(),
                ];
            }

            $parameters->set($output);

            $cache->save($parameters);
        //}

        return new JsonResponse($parameters->get());
    }

    public function searchAction(Request $request)
    {
				$webserviceTools       = $this->get('semappsBundle.webserviceTools');

				// Search
        return new JsonResponse(
          (object)[
            'results' => $webserviceTools->searchSparqlRequest(
              $request->get('t'),
              ''
              ,$request->get('f'),
							true
            ),
          ]
        );
    }

    public function fieldUriSearchAction(Request $request)
    {
				$webserviceTools       = $this->get('semappsBundle.webserviceTools');

				$output = [];
        // Get results.
        $results = $webserviceTools->searchSparqlRequest($request->get('QueryString'),$request->get('rdfType'));
        // Transform data to match to uri field (uri => title).
        foreach ($results as $item) {
            $output[$item['uri']] = $item['title'];
        }

        return new JsonResponse((object)$output);
    }



    public function fieldUriLabelAction(Request $request)
    {
				$webserviceTools       = $this->get('semappsBundle.webserviceTools');

				$label = $webserviceTools->sparqlGetLabel(
          $request->get('uri'),
          semappsConfig::Multiple
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
				$webserviceTools       = $this->get('semappsBundle.webserviceTools');

				return new JsonResponse(
          (object)[
            'detail' => $webserviceTools->requestPair($request->get('uri'),$this->entitiesTabs),
          ]
        );
    }

    public function ressourceAction(Request $request){
        $uri                = $request->get('uri');
        $sfClient           = $this->container->get('semantic_forms.client');
				$dbpediaConf				= $this->getParameter('dbpediaConf');
				$ressource      = $sfClient->dbPediaDetail($dbpediaConf,$uri,$request->getLanguages()[0]);
        $sparqlClient = new SparqlClient();
        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('pair','http://virtual-assembly.org/pair#')
            ->addSelect('?type')
            ->addSelect('?uri')
            ->addSelect('?image')
					->addSelect('( COALESCE(?firstName, "") As ?result_1)')
					->addSelect('( COALESCE(?lastName, "") As ?result_2)')
					->addSelect('( COALESCE(?name, "") As ?result_3)')
					->addSelect('(fn:concat(?result_3,?result_2, " ", ?result_1) as ?label)')
					->addWhere('?uri','rdf:type','?type','?gr')
					->addOptional('?uri','pair:firstName','?firstName','?gr')
					->addOptional('?uri','pair:lastName','?lastName','?gr')
					->addOptional('?uri','pair:preferedLabel','?name','?gr')
					->addOptional('?uri','pair:comment','?desc','?gr')
					->addOptional('?uri','pair:image','?image','?gr');
        $ressourceQuery = clone $sparql;
        $ressourceQuery->addWhere('?uri','pair:needs',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');

        $requests['ressourcesNeeded'] = $ressourceQuery->getQuery();
        $ressourceQuery = clone $sparql;
        $ressourceQuery->addWhere('?uri','pair:offers',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');
        $requests['ressourcesProposed'] =$ressourceQuery->getQuery();

				$ressourceQuery = clone $sparql;
				$ressourceQuery->addWhere('?uri','pair:hasSubject',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');
				$requests['hasSubject'] =$ressourceQuery->getQuery();

				$ressourceQuery = clone $sparql;
				$ressourceQuery->addWhere('?uri','pair:Skill',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');
				$requests['skill'] =$ressourceQuery->getQuery();


				$results['detail'] = $ressource;
				$results['uri'] = $uri;
        foreach ($requests as $key => $request){
            //dump($request);
            $resultsTemp = $sfClient->sparql($request);
						$results[$key]  = [];

						$resultsTemp = is_array($resultsTemp) ? $sfClient->sparqlResultsValues(
							$resultsTemp
            ) : [];

						$resultsTemp = $this->filter($resultsTemp);
						foreach ($resultsTemp as $resultTemp){
								if(array_key_exists('type',$resultTemp)){
										if(!array_key_exists($this->entitiesTabs[$resultTemp['type']]['nameType'],$results[$key]))
												$results[$key][$this->entitiesTabs[$resultTemp['type']]['nameType']] = [];

										array_push($results[$key][$this->entitiesTabs[$resultTemp['type']]['nameType']],$resultTemp);
								}
						}

        }
        return new JsonResponse(
            (object)[
                'ressource' => $results,
            ]
        );
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
