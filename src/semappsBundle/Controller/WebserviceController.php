<?php

namespace semappsBundle\Controller;

use semappsBundle\Entity\User;
use semappsBundle\Services\WebserviceCache;
use semappsBundle\Services\WebserviceTools;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;
use semappsBundle\coreConfig;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebserviceController
 * @package semappsBundle\Controller
 * @see WebserviceTools
 * utilisé par polymer principalement
 */
class WebserviceController extends Controller
{
    /**
     * @return JsonResponse
     * Envoie une liste de de paramètre pour la cartographie côté polymer
     * une liste de mot clé récupérée sur la base SPARQl
     * les informations sur l'utilisateur courant
     * table de liaison entre un type et son "nom machine"
     * table de liaison entre un nom de graph et son "nom machine"
     */
    public function parametersAction()
    {
        $cache = new FilesystemAdapter();
        $parameters = $cache->getItem('gv.webservice.parameters');
        $webserviceTools       = $this->get('semapps_bundle.webservice_tools');
        $contextManager        = $this->get('semapps_bundle.context_manager');
        $thematicConf        = $this->getParameter('thematicConf');
        //if (!$parameters->isHit()) {
        /** @var User $user */
        $user = $this->GetUser();
        // Get results.
        $results = $webserviceTools->searchSparqlRequest(
            '',
            coreConfig::URI_SKOS_THESAURUS,
            null,
            false,
            $thematicConf['graphuri']
        );

        $thesaurus = [];
        foreach ($results as $item) {
            $thesaurus[] = [
                'uri'   => $item['uri'],
                'label' => $item[$thematicConf['fields'][$thematicConf['label'][0]]['value']],
            ];
        }

        $access = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:User')
            ->getAccessLevelString($user);

        $graphUri = ($user)? $contextManager->getListOfContext($user->getSfLink(),$user->getId()) :null;
        $name = ($user != null)? $user->getUsername() : '';
        // If no internet, we use a cached version of services
        // placed int face_service folder.
        if ($this->container->hasParameter('no_internet')) {
            $output = ['no_internet' => 1];
        } else {
            $output = [
                'user'  => [
                    "name"  => $name,
                    "access"  => $access,
                    "uri"  => ($user != null)?$user->getSfLink() : null,
                    "graphuri"  => $graphUri
                ],
                'typeToName'      => $this->getParameter("typeToName"),
                'graphToName'      => $this->getParameter("graphToName"),
                'thesaurus'     => $thesaurus,
                'buildings'     => [],
//                'buildings'     => coreConfig::$buildings,
            ];
        }

        $parameters->set($output);

        $cache->save($parameters);
        //}

        return new JsonResponse($parameters->get());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * Lance une recherche globale
     */
    public function searchAction(Request $request)
    {
        $webserviceTools       = $this->get('semapps_bundle.webservice_tools');
        $confmanager       = $this->get('semapps_bundle.conf_manager');
        $sparqlRepository = $this->get('semapps_bundle.sparql_repository');
        $results =  $resultsTemp = $webserviceTools->searchSparqlRequest(
            $request->get('term'),
            $request->get('type'),
            $request->get('filter'),
            true
        );
        $confTemp = [];
        foreach ($resultsTemp as $uri=> $item){
            $uri = $item['uri'];
            $conf = (array_key_exists($item['type'],$confTemp))? $confTemp[$item['type']] : $confmanager->getConf($item['type'])['conf'] ;
            $isPublic = false;
            $isProtected = false;
            if(array_key_exists('access', $conf) && array_key_exists('public',$conf['access']))
                $isPublic = $sparqlRepository->checkIsPublic($uri,$conf,$sparqlRepository::ISPUBLIC);
            if(!$isPublic && array_key_exists('access', $conf) && array_key_exists('protected',$conf['access']))
                $isProtected = $sparqlRepository->checkIsPublic($uri,$conf,$sparqlRepository::ISPROTECTED);

            if(!$isPublic && array_key_exists('access', $conf) && array_key_exists('read',$conf['access'])){
                if(!$this->getUser() instanceof User ){
                    unset($results[$uri]);
                }else{
                    if(!$isProtected){
                        $arrayUri = $sparqlRepository->checkAccessWithGraph($uri,$conf,$sparqlRepository::READ,$this->getUser()->getSfLink());
                        if(array_key_exists('write',$conf['access'])){
                            $arrayUri = array_merge($arrayUri,$sparqlRepository->checkAccessWithGraph($uri,$conf,$sparqlRepository::WRITE,$this->getUser()->getSfLink()));
                        }
                        if(empty($arrayUri)){
                            unset($results[$uri]);
                        }
                    }
                }
            }
        }
        // Search
        return new JsonResponse(
            (object)[
                'results' => array_values($results),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * Lance une recherche sur un type particulier
     */
    public function fieldUriSearchAction(Request $request)
    {
        $webserviceTools       = $this->get('semapps_bundle.webservice_tools');
        $output = [];
        // Get results.
        $results = $webserviceTools->searchSparqlRequest($request->get('QueryString'),$request->get('rdfType'),null,false, $request->get('graphUri'));
        // Transform data to match to uri field (uri => title).
        foreach ($results as $item) {
            $output[$item['uri']] = $item['title'];
        }

        return new JsonResponse((object)$output);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * Récupère le label d'un uri
     */
    public function fieldUriLabelAction(Request $request)
    {
        $webserviceTools       = $this->get('semapps_bundle.webservice_tools');

        $label = $webserviceTools->sparqlGetLabel(
            $request->get('uri'),
            coreConfig::Multiple
        );

        return new JsonResponse(
            (object)['label' => $label]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * Envoie le détail complet d'une uri
     */
    public function detailAction(Request $request)
    {
        $webserviceTools       = $this->get('semapps_bundle.webservice_tools');
        return new JsonResponse(
            (object)[
                'detail' => $webserviceTools->requestPair($request->get('uri')),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * Envoie le détail complet d'une uri de type ressource (comme wikipedia par exemple)
     */
    public function ressourceAction(Request $request){
        $uri                = $request->get('uri');
        $sfClient           = $this->get('semantic_forms.client');
        $confManager           = $this->get('semapps_bundle.conf_manager');
        $dbpediaConf		= $this->getParameter('dbpediaConf');
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
        $ressourceQuery->addWhere('?uri','pair:hasKeyword',$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL),'?gr');
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
                    $componentConf =$confManager->getConf($resultTemp['type'])['conf'];
                    if(!array_key_exists($componentConf['nameType'],$results[$key]))
                        $results[$key][$componentConf['nameType']] = [];

                    array_push($results[$key][$componentConf['nameType']],$resultTemp);
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
     * @param $id
     * @return Response
     * Change le contexte d'une personne
     */
    public function changeContextAction($uri){
        $contextManager        = $this->get('semapps_bundle.context_manager');
        $contextManager->setContext($this->getUser()->getSfLink(),urldecode($uri));

        return new Response("ok",200);
    }


    /**
     * Filter only allowed types.
     * @param array $array
     * @return array
     */
    public function filter(Array $array){
        $filtered = [];
        $type = $this->getParameter('typeToName');
        foreach ($array as $result) {
            // Type is sometime missing.
            if (isset($result['type']) && array_key_exists(
                    $result['type'],
                    $type
                )
            ) {
                $filtered[] = $result;
            }
        }

        return $filtered;
    }


}
