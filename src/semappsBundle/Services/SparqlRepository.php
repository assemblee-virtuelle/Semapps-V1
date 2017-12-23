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

    public function __construct(SemanticFormsClient $sfClient){
        $this->sfClient = $sfClient;

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
}