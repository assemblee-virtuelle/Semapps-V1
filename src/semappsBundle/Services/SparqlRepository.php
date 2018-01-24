<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/12/2017
 * Time: 10:03
 */

namespace semappsBundle\Services;


use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use VirtualAssembly\SparqlBundle\Services\SparqlClient;

class SparqlRepository extends SparqlClient
{
    protected $sfClient;
    protected $confManager;

    public function __construct(SemanticFormsClient $sfClient,confManager $confManager){
        $this->sfClient = $sfClient;
        $this->confManager = $confManager;

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
        $componentConf = $this->confManager->getConf($type);
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
        $componentConf = $this->confManager->getConf($type);
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
        $componentConf = $this->confManager->getConf($type);
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
        return $this->sfClient->sparqlResultsValues($this->sfClient->sparql(" 
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
        ));
    }

    public function getListOfContentByType($type,$componentConf,$graphURI =null){

        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $this->newQuery($this::SPARQL_SELECT);
        $graphURI =($graphURI)? $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL):" ?GR ";
        $componentType = $sparql->formatValue($type,$sparql::VALUE_TYPE_URL);

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

        $listContent = [];
        if (isset($results["results"]["bindings"])) {
            foreach ($results["results"]["bindings"] as $item) {
                $title = '';
                foreach ($componentConf['label'] as $field ){
                    $label = $componentConf['fields'][$field]['value'];
                    $title .= $item[$label]['value'] .' ';
                }
                $listContent[] = [
                    'uri'   => $item['URI']['value'],
                    'title' => $title,
                ];

            }
        }
        return $listContent;
    }
}