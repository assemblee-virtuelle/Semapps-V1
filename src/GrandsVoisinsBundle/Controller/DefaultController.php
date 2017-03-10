<?php

namespace GrandsVoisinsBundle\Controller;

class DefaultController extends AbstractController
{
    public function indexAction()
    {
        $nom = 'prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                    SELECT ?G ?O
                    WHERE 
                    {
                        {
                            GRAPH ?G 
                            {
                                ?S rdf:type foaf:Organization .
                                ?S <http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#administrativeName> ?O.
                            }
                        }
                    }';
        $responsable ='prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                    SELECT ?G ?O
                    WHERE 
                    {
                        {
                            GRAPH ?G 
                            {
                                ?S rdf:type foaf:Organization .
                                ?S <http://virtual-assembly.org/pair_v2#hasResponsible> ?O.
                            }
                        }
                    }';
        $batiment ='prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                    SELECT ?G ?O
                    WHERE 
                    {
                        {
                            GRAPH ?G 
                            {
                                ?S rdf:type foaf:Organization .
                                ?S <http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#building> ?O.
                            }
                        }
                    }';

        $personne='prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                    SELECT ?O
                    WHERE 
                    {
                        {
                            GRAPH <urn:gv/contacts/row/112-org>
                            {
                               ?S <http://xmlns.com/foaf/0.1/mbox>  ?O.
                            }
                        }
                    }';

        // URI may point to a place outside SF, but
        // a lot of them should come from here, so we
        // have to authenticate.
        $sfClient = $this->container->get('semantic_forms.client');

        $nomRes = $sfClient->sparql($nom)["results"]["bindings"];
        $responsableRes = $sfClient->sparql($responsable)["results"]["bindings"];
        $batimentRes = $sfClient->sparql($batiment)["results"]["bindings"];
        $tab =array();
        $i=0;
        foreach ($nomRes as $element){
            $tab[$element["G"]["value"] ] = array($responsableRes[$i]["O"]["value"],$element["O"]["value"]);
            $i++;
        }

        foreach ($batimentRes as $batiment){
            array_push($tab[$batiment["G"]["value"]],$batiment["O"]["value"]);
        }

        foreach ($tab as $elem){
            $personne='prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                    SELECT ?O
                    WHERE 
                    {
                        {
                            GRAPH <'.$elem[0].'>
                            {
                               ?S <http://xmlns.com/foaf/0.1/mbox>  ?O.
                            }
                        }
                    }';
            $personneRes = $sfClient->sparql($personne);//["results"]["bindings"]["O"]["value"];
            dump($personneRes);

        }

        return $this->render('GrandsVoisinsBundle:Default:index.html.twig',array('o' =>$tab,'g' => sizeof($batimentRes)));
    }

    public function searchAction()
    {
        return $this->render('GrandsVoisinsBundle:Default:index.html.twig');
    }
}
