<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 18/01/18
 * Time: 11:25
 */

namespace semappsBundle\Services;


use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

class ImportManager
{
    /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
    private $sfClient;
    /** @var \semappsBundle\Services\SparqlRepository $sparqlRepository */
    private $sparqlRepository;

    public function __construct(SemanticFormsClient $sfClient,SparqlRepository $sparqlRepository)
    {
        $this->sfClient = $sfClient;
        $this->sparqlRepository = $sparqlRepository;
    }

    public function actualize($uri){
        $this->removeUri($uri);
        $this->sfClient->import($uri);
    }

    public function changeUri($oldUri,$newUri){
        $this->removeUri($oldUri);
        $this->sfClient->import($newUri);
    }
    public function removeUri($uri){

        $sparql = $this->sparqlRepository->newQuery($this->sparqlRepository::SPARQL_DELETE);
        $sparqlDeux = clone $sparql;

        $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

        $sparql->addDelete($uri,'?P','?O','?gr')
            ->addWhere($uri,'?P','?O','?gr');
        $sparqlDeux->addDelete('?s','?PP',$uri,'?gr')
            ->addWhere('?s','?PP',$uri,'?gr');

        $this->sfClient->update($sparql->getQuery());
        $this->sfClient->update($sparqlDeux->getQuery());
    }
}