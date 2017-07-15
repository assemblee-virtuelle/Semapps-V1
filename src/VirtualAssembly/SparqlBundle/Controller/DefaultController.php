<?php

namespace VirtualAssembly\SparqlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $sparqlClient = new SparqlClient();
        // requete dans admin

        /*
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_DELETE);
        //$sparql = new sparqlSelect();
        $sparql->addPrefixes($this->prefixes);
        $sparql->addDelete(
          $sparql->formatValue('http://dev.wexample.com:9000/ldp/1494411553449-288024850710237', $sparql::VALUE_TYPE_URL),
          'foaf:img',
          '?o',
          $sparql->formatValue('urn:gv/contacts/new/row/215-org',$sparql::VALUE_TYPE_URL));
        $sparql->addWhere(
          $sparql->formatValue('http://dev.wexample.com:9000/ldp/1494411553449-288024850710237', $sparql::VALUE_TYPE_URL),
          'foaf:img',
          '?o',
          $sparql->formatValue('urn:gv/contacts/new/row/215-org',$sparql::VALUE_TYPE_URL));
        */
        /*
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_INSERT_DATA);
        $sparql->addPrefixes($this->prefixes);
        $sparql->addInsert(
          $sparql->formatValue('http://dev.wexample.com:9000/ldp/1494411553449-288024850710237', $sparql::VALUE_TYPE_URL),
          'foaf:img',
          $sparql->formatValue('http://reseau.lesgrandsvoisins.org/uploads/pictures/9dc1c5bb18c9ec2dc6ddea75eea2834a.png',$sparql::VALUE_TYPE_URL),
          $sparql->formatValue('urn:gv/contacts/new/row/215-org',$sparql::VALUE_TYPE_URL));
        */

        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        /*
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($this->prefixes);
        $sparql->addSelect('?name');
        $sparql->addSelect('?forname');
        $sparql->addOptional($sparql->formatValue('http://localhost:9000/ldp/1495790423454-178611955791819',$sparql::VALUE_TYPE_URL),
          'foaf:familyName',
          '?name',
          $sparql->formatValue('user:aa',$sparql::VALUE_TYPE_URL));

        $sparql->addOptional($sparql->formatValue('http://localhost:9000/ldp/1495790423454-178611955791819',$sparql::VALUE_TYPE_URL),
          'foaf:givenName',
          '?forname',
          $sparql->formatValue('user:aa',$sparql::VALUE_TYPE_URL));
        */
        /*
                $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addSelect('?G');
                $sparql->addSelect('?P');
                $sparql->addSelect('?O');
                $sparql->addWhere('?s','rdf:type','foaf:Organization','?G');
                $sparql->addWhere('?s','?P','?O','?G');
                $sparql->groupBy('?G');
                $sparql->groupBy('?P');
                $sparql->groupBy('?O');
        */

        //component controller
        /*
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addPrefixes($this->prefixes);
        $sparql->addSelect('?URI');
        $sparql->addSelect('?NAME');
        $sparql->addWhere('?URI','rdf:type','foaf:Project','?G');
        $sparql->addWhere('?URI','rdfs:label','?NAME','?G');
        */
        /*
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_DELETE);

        $sparql->addPrefixes($this->prefixes);
        $sparql->addDelete($sparql->formatValue('http://localhost:9000/ldp/1495790423454-178611955791819',$sparql::VALUE_TYPE_URL),
         '?p','?o','?gr');
        $sparql->addDelete('?s',
         '?pp',$sparql->formatValue('http://localhost:9000/ldp/1495790423454-178611955791819',$sparql::VALUE_TYPE_URL),'?gr');
        $sparql->addWhere('?URI','rdf:type','foaf:Project','?G');
        $sparql->addWhere('?URI','rdfs:label','?NAME','?G');
       */
        $sparql = $sparqlClient->newQuery(SparqlClient::SPARQL_SELECT);
        $sparql->addSelect('?s')
          ->addWhere('?s','?p','?o','?g');
        $head['?test'][] = $sparql->createTriple('?s','?p','?o');
        $head[] = $sparql->createTriple('?s','?p','?o');
        $head[] = $sparql->createTriple('?s','?p','?o');
        $head[] = $sparql->createTriple('?s','?p','?o');
        $union[] = $sparql->createTriple('?s','?p','?o');
        $union[] = $sparql->createTriple('?s','?p','?o');
        $union[] = $sparql->createTriple('?s','?p','?o');
        $query = $sparql->addUnion($head,$union)->getQuery();
        return $this->render('AVSparqlBundle:Default:index.html.twig',['query' =>$query]);

    }
}
