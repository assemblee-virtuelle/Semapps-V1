<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;

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

        // URI may point to a place outside SF, but
        // a lot of them should come from here, so we
        // have to authenticate.
        $sfClient = $this->container->get('semantic_forms.client');

        $nomRes = $sfClient->sparql($nom)["results"]["bindings"];
        $responsableRes = $sfClient->sparql($responsable)["results"]["bindings"];
        $batimentRes = $sfClient->sparql($batiment)["results"]["bindings"];
        $tab =array();
        $noThisOrga= ["urn:gv/contacts/row/112-org"];

        foreach ($responsableRes as $nom){
            if(!in_array($nom["G"]["value"],$noThisOrga))
                $tab[$nom["G"]["value"] ] = array($nom["O"]["value"]);
        }

        foreach ($nomRes as $nom){
            if(!in_array($nom["G"]["value"],$noThisOrga))
                array_push($tab[$nom["G"]["value"]],$nom["O"]["value"]);
        }

        foreach ($batimentRes as $batiment){
            if(!in_array($batiment["G"]["value"],$noThisOrga))
                array_push($tab[$batiment["G"]["value"]],$batiment["O"]["value"]);
        }
        $tabwith2 = array();
        $tabwith3 = array();
        foreach ($tab as $key => $value){
            if(count($value) >= 3 || $key == 'urn:gv/contacts/row/247-org')
                $tabwith3[$key] = $value;
            else
                $tabwith2[$key] = $value;
        }
        $email = array();
        $email2 = array();
        foreach ($tabwith3 as $key => $value){
            $personne='prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                    SELECT ?O
                    WHERE
                    {
                        {
                            GRAPH <'.$key.'>
                            {
                               ?S <http://xmlns.com/foaf/0.1/mbox>  ?O.
                            }
                        }
                    }';
            $personneRes = $sfClient->sparql($personne)["results"]["bindings"][0]["O"]["value"];//["results"]["bindings"]["O"]["value"];
            if(strpos($personneRes,'<') !== false){
                $personneRes = str_replace(['<','>'],'',stristr($personneRes,'<'));
            }
            $personneRes = trim(explode('/',$personneRes)[0]);
            $personneRes = trim(explode(',',$personneRes)[0]);

            if(in_array($personneRes,$email)){
                $tabwith3[$key][0] = $personneRes;
                $email2[$key] = $tabwith3[$key];
                unset($tabwith3[$key]);

            }
            else {
                array_push($email,$personneRes);
                $tabwith3[$key][0] = $personneRes;
            }


        }
        dump($email2);
        //dump($tabwith3);

        $orga = array();
        foreach ($tabwith3 as $key=>$tab){
            set_time_limit ( 30 );
            if(!in_array($tab[1],$orga)){
                array_push($orga,$tab[1]);
            }else{
                $tokenGenerator = $this->container->get(
                    'fos_user.util.token_generator'
                );
                $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
                $token = $tokenGenerator->generateToken();
                dump($key,$tab,$randomPassword,$token);
            }
        }
        dump("ok");
        exit;
    }

}