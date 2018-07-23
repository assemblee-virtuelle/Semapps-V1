<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 28/11/2017
 * Time: 10:14
 */

namespace semappsBundle\Services;


use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use semappsBundle\Entity\User;
use semappsBundle\coreConfig;
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
    /** @var  ConfManager */
    protected $confmanager;
    /** @var WebserviceCache  */
    protected $webserviceCache;

    protected $sparqlRepository;

    protected $parser;
    public function __construct(EntityManager $em, TokenStorage $tokenStorage, AuthorizationChecker $checker, ConfManager $confmanager, SemanticFormsClient $sfClient, WebserviceCache $webserviceCache, MarkdownParserInterface $parser, SparqlRepository $sparqlRepository){
        $this->sfClient = $sfClient;
        $this->em = $em;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->confmanager = $confmanager;
        $this->webserviceCache = $webserviceCache;
        $this->parser = $parser;
        $this->sparqlRepository = $sparqlRepository;
    }
    public function searchSparqlRequest($term, $type = coreConfig::Multiple, $filter=null, $isBlocked = false,$graphUri = null)
    {

        $arrayType = explode('|',$type);

        $sparqlClient = new SparqlClient();
        $results = [];

        foreach ($arrayType as $type) {
            $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
            $confType = $this->confmanager->getConf($type,[$graphUri => '0'])['conf'];
            $fields = $confType['fields'];
            $arrayOfPred = $confType['label'];
            $first = array_shift($arrayOfPred);
            $sparql->addPrefixes($sparql->prefixes)
            ->addSelect('?uri')
            ->addSelect('?type')
            ->addSelect('?'.$fields[$first]['value'])
            ->addWhere('?uri','rdf:type', '?type',($graphUri)? "<".$graphUri.">" :'?GR')
            ->addWhere('?uri','rdf:type', $sparql->formatValue($type,$sparql::VALUE_TYPE_URL),($graphUri)? "<".$graphUri.">" :'?GR')
            ->addWhere('?uri',$sparql->formatValue($first,$sparql::VALUE_TYPE_URL),'?'.$fields[$first]['value'],($graphUri)? "<".$graphUri.">" :'?GR')
            ->orderBy($sparql::ORDER_ASC,'?'.$fields[$first]['value'])
            ->groupBy('?uri ?type ?'.$fields[$first]['value']);

            ($filter)? $sparql->addWhere('?uri','?p',$sparql->formatValue($filter,$sparql::VALUE_TYPE_URL),($graphUri)? "<".$graphUri.">" :'?GR' ) : null;
            $filtersLine = ($term)? 'contains( lcase(?'.$fields[$first]['value'].') , lcase("'.$term.'")) ' : '';
            if (is_array($arrayOfPred)){
                foreach ($arrayOfPred as $predicat){
                    $sparql->addSelect('?'.$fields[$predicat]['value'])
                        ->addOptional('?uri',$sparql->formatValue($predicat,$sparql::VALUE_TYPE_URL),'?'.$fields[$predicat]['value'],($graphUri)? "<".$graphUri.">" :'?GR')
                        ->groupBy('?'.$fields[$predicat]['value']);

                    if($term)$filtersLine .='|| contains( lcase(?'.$fields[$predicat]['value'].') , lcase("'.$term.'")) ';
                }
            }

            if($term)$sparql->addFilter($filtersLine);
            $results = array_merge($results,$this->sfClient->sparqlResultsValues($this->sfClient->sparql($sparql->getQuery()), 'uri'));
        }
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
            case coreConfig::URI_PAIR_PERSON :
                $sparql->addSelect('( COALESCE(?lastName, "") As ?result)  (fn:concat(?firstName, " ", ?result) as ?label)')
                    ->addWhere('?uri','pair:firstName','?firstName','?gr')
                    ->addOptional('?uri','pair:lastName','?lastName','?gr');

                break;
            case coreConfig::URI_PAIR_ORGANIZATION :
            case coreConfig::URI_PAIR_PROJECT :
            case coreConfig::URI_PAIR_PROPOSAL :
            case coreConfig::URI_PAIR_EVENT :
            case coreConfig::URI_PAIR_DOCUMENT :
            case coreConfig::URI_PAIR_GOOD :
            case coreConfig::URI_PAIR_SERVICE :
            case coreConfig::URI_PAIR_PLACE :
                $sparql->addSelect('?label')
                    ->addWhere('?uri','pair:preferedLabel','?label','?gr');

                break;
            case coreConfig::URI_SKOS_THESAURUS:
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
        $dbpediaConf = $this->confmanager->getConf()['conf'];
        $componentConfComplete = $this->confmanager->getConf($properties['key'],array_flip(explode(',',$properties['graph'])));
        $componentConf = $componentConfComplete['conf'];
        $properties['type'] = [$componentConf['type']];
        $propertiesWithUri=[];
        foreach ($componentConf['fields'] as $predicat =>$detail){
            $simpleKey = $detail['value'];

            if (isset($properties[$simpleKey])){
                switch ( (array_key_exists('type',$detail)?$detail['type'] : null) ){
                    case 'uri':
                        $propertiesWithUri[] = $simpleKey;
                        break;
                    case 'dbpedia':
                        foreach ($properties[$simpleKey] as $uri) {
                            $label = $this->webserviceCache->getContent('dbpedia',$uri);
                            if(!$label){
                                $label = $this->sfClient->dbPediaLabel($dbpediaConf,$uri);
                                $this->webserviceCache->setContent('dbpedia',$uri,$label);
                            }
                            if ($label)
                                $output[$simpleKey][] = [
                                    'uri'  => $uri,
                                    'name' => $label,
                                ];
                        }
                        break;
                    default:
                        switch ($simpleKey){
                            case 'description':
//                                $properties[$simpleKey] = nl2br(current($properties[$simpleKey]),false);
                                $properties[$simpleKey] = $this->parser->transformMarkdown(current($properties[$simpleKey]));
                                break;

                            case 'hasInterest':
                                foreach ($properties[$simpleKey] as $uri) {
                                    $result = [
                                        'uri' => $uri,
                                        'name' => $this->sparqlGetLabel($uri,coreConfig::URI_SKOS_THESAURUS)
                                    ];
                                    $output[$simpleKey][] = $result;
                                }
                                break;
                        }
                        break;
                }
            }
        }

        switch (current($properties['type'])) {
            // Orga.
            case  coreConfig::URI_PAIR_ORGANIZATION:
                $output['title'] = current($properties['preferedLabel']);
                break;
            // Person.
            case  coreConfig::URI_PAIR_PERSON:
                $output ['title'] = current($properties['firstName']).' '.current($properties['lastName']);
                break;
            // Project.
            case coreConfig::URI_PAIR_PROJECT:
                $output['title'] = current($properties['preferedLabel']);
                break;
            // Event.
            case coreConfig::URI_PAIR_EVENT:
                $output['title'] = current($properties['preferedLabel']);
                break;
            // Proposition.
            case coreConfig::URI_PAIR_PROPOSAL:
                $output['title'] = current($properties['preferedLabel']);
                break;
            // document
            case coreConfig::URI_PAIR_DOCUMENT:
                $output['title'] = current($properties['preferedLabel']);
                break;
            case coreConfig::URI_PAIR_GOOD:
                $output['title'] = current($properties['preferedLabel']);
                break;
            case coreConfig::URI_PAIR_SERVICE:
                $output['title'] = current($properties['preferedLabel']);
                break;
            case coreConfig::URI_PAIR_PLACE:
                $output['title'] = current($properties['preferedLabel']);
                break;
            case coreConfig::URI_SKOS_CONCEPT:
                $output['title'] = current($properties['preferedLabel']);
                break;
        }
        $this->getData($properties,$propertiesWithUri,$output);
        $output['properties'] = $properties;

        //dump($output);
        return $output;

    }

    private function getData(&$properties,$tabFieldsAlias,&$output){
        $cacheTemp = [];
        $cacheComponentConf = [];
        foreach ($tabFieldsAlias as $alias) {
            if (isset($properties[$alias])) {
                foreach (array_unique($properties[$alias]) as $uri) {
                    if (array_key_exists($uri, $cacheTemp)) {
                        $output[$alias][$cacheComponentConf[$cacheTemp[$uri]['type']]['nameType']][] = $cacheTemp[$uri];
                    } else {
                        $component = $this->uriPropertiesFiltered($uri);
                        if(array_key_exists('type',$component)){
                            $componentType = current($component['type']);
                            if(array_key_exists($componentType,$cacheComponentConf)){
                                $componentConf = $cacheComponentConf[$componentType];
                            }
                            else{
                                $componentConfComplete = $this->confmanager->getConf($component['key'],array_flip(explode(',',$component['graph'])));
                                $componentConf = $cacheComponentConf[$componentType] =$componentConfComplete['conf'];
                            }
                            $isAllowed = true;
                            $isPublic = false;
                            $isProtected = false;
                            if(array_key_exists('access', $componentConf) && array_key_exists('public',$componentConf['access']))
                                $isPublic = $this->sparqlRepository->checkIsPublic($uri,$componentConf,$this->sparqlRepository::ISPUBLIC);
                            if(!$isPublic && array_key_exists('access', $componentConf) && array_key_exists('protected',$componentConf['access']))
                                $isProtected = $this->sparqlRepository->checkIsPublic($uri,$componentConf,$this->sparqlRepository::ISPROTECTED);


                            if(!$isPublic && array_key_exists('access', $componentConf) && array_key_exists('read',$componentConf['access'])){
                                if(!$this->tokenStorage->getToken()->getUser() instanceof User ){
                                    $isAllowed= false;
                                }else{
                                    if(!$isProtected){
                                        $arrayUri = $this->sparqlRepository->checkAccessWithGraph($uri,$componentConf,$this->sparqlRepository::READ,$this->tokenStorage->getToken()->getUser()->getSfLink());
                                        if(array_key_exists('write',$componentConf['access'])){
                                            $arrayUri = array_merge($arrayUri,$this->sparqlRepository->checkAccessWithGraph($uri,$componentConf,$this->sparqlRepository::WRITE,$this->tokenStorage->getToken()->getUser()->getSfLink()));
                                        }
                                        if(empty($arrayUri)){
                                            $isAllowed =false;
                                        }
                                    }
                                }
                            }

                            $result = null;
                            if($isAllowed){
                                switch ($componentConf['type']) {
                                    case coreConfig::URI_PAIR_PERSON:
                                        $result = [
                                            'uri' => $uri,
                                            'name' => ((current($component['firstName'])) ? current($component['firstName']) : "") . " " . ((current($component['lastName'])) ? current($component['lastName']) : ""),
                                            'image' => (!isset($component['image'])) ? '/common/images/no_avatar.png' : $component['image'],
                                        ];
                                        $output[$alias][$componentConf['nameType']][] = $result;
                                        break;
                                    case coreConfig::URI_SKOS_CONCEPT:
                                    case coreConfig::URI_PAIR_ORGANIZATION:
                                    case coreConfig::URI_PAIR_PROJECT:
                                    case coreConfig::URI_PAIR_EVENT:
                                    case coreConfig::URI_PAIR_PROPOSAL:
                                    case coreConfig::URI_PAIR_DOCUMENT:
                                    case coreConfig::URI_PAIR_GOOD:
                                    case coreConfig::URI_PAIR_SERVICE:
                                    case coreConfig::URI_PAIR_PLACE:
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
                            }else{
                                unset($properties[$alias]);
                            }
                            usort($output[$alias][$componentConf['nameType']],function ($a,$b){
                                return strcmp($a["name"], $b["name"]);
                            });
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
            $componentConfComplete = $this->confmanager->getConf($properties[self::TYPE],array_flip($properties['graph']));
            $sfConf = $componentConfComplete['conf'];
            foreach ($sfConf['fields'] as $field =>$detail){
                if ($detail['access'] === 'anonymous' ||
                    $this->checker->isGranted('ROLE_'.strtoupper($detail['access']))

                ){
                    if (isset($properties[$field])) {
                        $output[$detail['value']] = $properties[$field];
                    }
                }
            }
            $output['key'] =$componentConfComplete['key'];
        }
        return $output;
    }

}