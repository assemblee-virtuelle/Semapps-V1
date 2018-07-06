<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/12/2017
 * Time: 10:03
 */

namespace semappsBundle\Services;


use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;

class SparqlRepository extends SparqlClient
{
    protected $sfClient;
    protected $confManager;
    protected $token;
    const READ = 'read';
    const WRITE= 'write';
    const ISPUBLIC= 'public';
    const ISPROTECTED= 'protected';
    public function __construct(SemanticFormsClient $sfClient, ConfManager $confManager, TokenStorage $token){
        $this->sfClient = $sfClient;
        $this->confManager = $confManager;
        $this->token = $token;
    }
    public function changeImage($graph,$uri,$newImage){
        $sparql = $this->newQuery(self::SPARQL_DELETE);
        $sparql->addPrefixes($sparql->prefixes)
            ->addPrefix('pair','http://virtual-assembly.org/pair#')
            ->addDelete(
                $sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
                'pair:image',
                '?o',
                $sparql->formatValue($graph,$sparql::VALUE_TYPE_URL))
            ->addWhere(
                $sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
                'pair:image',
                '?o',
                $sparql->formatValue($graph,$sparql::VALUE_TYPE_URL));
        $this->sfClient->update($sparql->getQuery());
        //dump($sparql->getQuery());
        $sparql = $this->newQuery(self::SPARQL_INSERT_DATA);
        $sparql->addPrefixes($sparql->prefixes)
            ->addPrefix('pair','http://virtual-assembly.org/pair#')
            ->addInsert(
                $sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
                'pair:image',
                $sparql->formatValue($newImage,$sparql::VALUE_TYPE_TEXT),
                $sparql->formatValue($graph,$sparql::VALUE_TYPE_URL));
        $this->sfClient->update($sparql->getQuery());
    }

    public function getLabel($type,$graphURI){
        $componentConf = $this->confManager->getConf($type)['conf'];
        $results = null;
        if ($componentConf['label']){
            /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
            $sparql = $this->newQuery(self::SPARQL_SELECT);
            $graphURI = $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL);
            $componentType = $sparql->formatValue($type,$sparql::VALUE_TYPE_URL);

            $sparql->addPrefixes($sparql->prefixes)
                ->addSelect('?URI')
                ->addWhere('?URI','rdf:type',$componentType,$graphURI);
            foreach ($componentConf['label'] as $field ){
                $label = $componentConf['fields'][$field]['value'];
                $fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
                $sparql->addSelect('?'.$label)
                    ->addOptional('?URI',$fieldFormatted,'?'.$label,$graphURI);
            }

            $results = $this->sfClient->sparql($sparql->getQuery());
        }

        return $results;
    }

    public function getImage($type,$graphURI){
        $componentConf = $this->confManager->getConf($type)['conf'];
        $results = null;
        if ($componentConf['image']){
            /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
            $sparql = $this->newQuery(self::SPARQL_SELECT);
            $graphURI = $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL);
            $componentType = $sparql->formatValue($type,$sparql::VALUE_TYPE_URL);

            $sparql->addPrefixes($sparql->prefixes)
                ->addSelect('?URI')
                ->addWhere('?URI','rdf:type',$componentType,$graphURI);
            foreach ($componentConf['image'] as $field ){
                $label = $componentConf['fields'][$field]['value'];
                $fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
                $sparql->addSelect('?'.$label)
                    ->addOptional('?URI',$fieldFormatted,'?'.$label,$graphURI);
            }

            $results = $this->sfClient->sparql($sparql->getQuery());
        }

        return $results;
    }

    public function getLabelAndImage($type,$graphURI){
        $componentConf = $this->confManager->getConf($type)['conf'];
        $results = null;
        if ($componentConf['image'] || $componentConf['image']) {
            /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
            $sparql = $this->newQuery(self::SPARQL_SELECT);
            $graphURI = $sparql->formatValue($graphURI, $sparql::VALUE_TYPE_URL);
            $componentType = $sparql->formatValue($type, $sparql::VALUE_TYPE_URL);

            $sparql->addPrefixes($sparql->prefixes)
                ->addSelect('?URI')
                ->addWhere('?URI', 'rdf:type', $componentType, $graphURI);
            if($componentConf['label']){
                foreach ($componentConf['label'] as $field) {
                    $label = $componentConf['fields'][$field]['value'];
                    $fieldFormatted = $sparql->formatValue($field, $sparql::VALUE_TYPE_URL);
                    $sparql->addSelect('?' . $label)
                        ->addOptional('?URI', $fieldFormatted, '?' . $label, $graphURI);
                }
            }
            if($componentConf['image']) {
                foreach ($componentConf['image'] as $field) {
                    $label = $componentConf['fields'][$field]['value'];
                    $fieldFormatted = $sparql->formatValue($field, $sparql::VALUE_TYPE_URL);
                    $sparql->addSelect('?' . $label)
                        ->addOptional('?URI', $fieldFormatted, '?' . $label, $graphURI);
                }
            }
            $results = $this->sfClient->sparql($sparql->getQuery());
        }
        return $results;
    }

    public function getAllowedGraphOfCurrentUser($sfLink){

        $result = $this->sfClient->sparqlResultsValues($this->sfClient->sparql(" 
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX pair: <http://virtual-assembly.org/pair#>
        SELECT ?G ?O
        WHERE {GRAPH ?G 
        {
            {?s pair:preferedLabel ?O. ?s rdf:type pair:Organization . ?s pair:hasMember <".$sfLink.">. }
            UNION 
                { ?s pair:preferedLabel ?O. ?s rdf:type pair:Organization . ?s pair:hasResponsible <".$sfLink.">.}
            UNION 
                {?s pair:preferedLabel ?O. ?s rdf:type pair:Organization. ?s pair:employs <".$sfLink.">.}
            }
        }
        GROUP BY ?G ?O"
        ),'G');
        $resultTemp = [];
        foreach (array_keys($result) as $organization){
            $resultTemp = array_merge($resultTemp,$this->sfClient->sparqlResultsValues($this->sfClient->sparql(" 
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX pair: <http://virtual-assembly.org/pair#>
            SELECT ?G ?O
            WHERE {GRAPH ?G 
            {
                ?s pair:preferedLabel ?O. ?s rdf:type pair:Organization . ?s pair:isPartnerOf <".$organization.">. 
            }}
            GROUP BY ?G ?O"
            ),'G'));

        }
        return array_merge($resultTemp,$result);
    }

    public function getListOfContentByType($componentConf,$graphURI =null){

        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $this->newQuery($this::SPARQL_SELECT);
        $graphURI =($graphURI)? $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL):" ?GR ";
        $componentType = $sparql->formatValue($componentConf['type'],$sparql::VALUE_TYPE_URL);
        $listContent = [];
        if(array_key_exists('access',$componentConf) && array_key_exists('write',$componentConf['access']) ){

            $arrayUri = array_keys($this->checkAccessWithUri($graphURI,$componentConf,$this::WRITE,$this->token->getToken()->getUser()->getSfLink()));

            foreach (array_unique($arrayUri) as $uri){
                $sparql = $this->newQuery($this::SPARQL_SELECT);

                /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addWhere('<'.$uri.'>','rdf:type',$componentType,$graphURI);

                foreach ($componentConf['label'] as $field ){
                    $label = $componentConf['fields'][$field]['value'];
                    $fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
                    $sparql->addSelect('?'.$label)
                        ->addWhere('<'.$uri.'>',$fieldFormatted,'?'.$label,$graphURI);
                }
                $sparql->orderBy($sparql::ORDER_ASC,'?'.$componentConf['fields'][$componentConf['label'][0]]['value']);
                $results = $this->sfClient->sparql($sparql->getQuery());
                if (isset($results["results"]["bindings"])) {
                    foreach ($results["results"]["bindings"] as $item) {
//                        $title = '';
                        $detailListContent=[];
                        foreach ($componentConf['label'] as $field ){
                            $label = $componentConf['fields'][$field]['value'];
                            $detailListContent[$label] = $item[$label]['value'];
//                            $title .= $item[$label]['value'] .' ';
                        }
                        $listContent[$uri] = [
                            'uri'   => $uri,
                            'content' => $detailListContent,
                            'graph' => (array_key_exists('GR',$item))? $item['GR']['value']:$graphURI,
                        ];
                    }
                }
            }
        }else{
            $sparql->addPrefixes($sparql->prefixes)
                ->addSelect('?URI')
                ->addWhere('?URI','rdf:type',$componentType,$graphURI);
            foreach ($componentConf['label'] as $field ){
                $label = $componentConf['fields'][$field]['value'];
                $fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
                $sparql->addSelect('?'.$label)
                    ->addWhere('?URI',$fieldFormatted,'?'.$label,$graphURI);
            }
            $results = $this->sfClient->sparql($sparql->getQuery());
            if (isset($results["results"]["bindings"])) {
                foreach ($results["results"]["bindings"] as $item) {
//                    $title = '';
                    $detailListContent=[];
                    foreach ($componentConf['label'] as $field ){
                        $label = $componentConf['fields'][$field]['value'];
                        $detailListContent[$label] = $item[$label]['value'];
//                        $title .= $item[$label]['value'] .' ';
                    }
                    $listContent[$item['URI']['value']] = [
                        'uri'   => $item['URI']['value'],
                        'content' => $detailListContent,
                        'graph' => (array_key_exists('GR',$item))? $item['GR']['value']:$graphURI,
                    ];
                }
            }
        }
//        dump($listContent);exit;
        ksort($listContent);
        return $listContent;
    }

    public function checkAccessWithUri($graphURI,$componentConf,$access,$userUri){

        $contexts = array_keys($this->getAllowedGraphOfCurrentUser($userUri));
        $contexts[] = $userUri;

        return  $this->checkAccess('?URI','?URI','<'.$componentConf['access'][$access].'>',$contexts,$graphURI);
    }

    public function checkAccessWithGraph($uri,$componentConf,$access,$userUri){

        $contexts = array_keys($this->getAllowedGraphOfCurrentUser($userUri));
        $contexts[] = $userUri;

        return $this->checkAccess('?GR','<'.$uri.'>','<'.$componentConf['access'][$access].'>',$contexts,'?GR');
    }
    public function checkIsPublic($uri,$componentConf,$access){
        $sparql = $this->newQuery($this::SPARQL_SELECT);
        $sparql->addSelect("?GR");
        $sparql->addWhere('<'.$uri.'>','<'.$componentConf['access'][$access].'>','"1"',"?GR");
        return !empty($this->sfClient->sparqlResultsValues($this->sfClient->sparql($sparql->getQuery()),"GR"));
    }

    private function checkAccess($select,$subject,$predicat,$contextList,$graph){
        $result = [];
        foreach($contextList as $context){
            $sparql = $this->newQuery($this::SPARQL_SELECT);
            $sparql->addSelect($select);
            $sparql->addWhere($subject,$predicat,'<'.$context.'>',$graph);
            $result = array_merge($result,$this->sfClient->sparqlResultsValues($this->sfClient->sparql($sparql->getQuery()),ltrim($select,'?')));
        }
        return $result;
    }

}