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
        $i=0;
        $orga = array();
        foreach ($nomRes as $element){
            $tab[$element["G"]["value"] ] = array($responsableRes[$i]["O"]["value"],$element["O"]["value"]);
            $i++;
        }

        foreach ($batimentRes as $batiment){
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
        dump($tabwith3);
        $orga = array();
        $em = $this->getDoctrine()->getManager();
        $orgaRepository = $em->getRepository('GrandsVoisinsBundle:Organisation');
        $i =0;
        foreach ($tabwith3 as $key=>$tab){
            set_time_limit ( 30 );

            if(!in_array($tab[1],$orga)){
                $organisation = new Organisation();
                $user = new User();
                //for the organisation
                $organisation->setBatiment(key_exists(2,$tab)? $tab[2] : 'Robin');
                $organisation->setName($tab[1]);
                array_push($orga,$tab[1]);
                $organisation->setSfOrganisation($key);
                // tells Doctrine you want to (eventually) save the Product (no queries yet)
                $em->persist($organisation);
                $organisation->setGraphURI(
                    $key
                );
                // actually executes the queries (i.e. the INSERT query)
                try {
                    $em->flush($organisation);
                } catch (UniqueConstraintViolationException $e) {
                        dump("le nom de l'orgnanisation que vous avez saisi est déjà présent");
                    exit;
                    //return $this->redirectToRoute('all_orga');
                }
                //TODO find a way to call teamAction in admin
                //for the user
                $user->setUsername(stristr($tab[0],'@',true).$i);
                $i++;
                $user->setEmail($tab[0]);

                // Generate password.
                $tokenGenerator = $this->container->get(
                    'fos_user.util.token_generator'
                );
                $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
                $user->setPassword(
                    password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
                );

                $user->setSfUser($randomPassword);
                $user->setSfLink(stristr($key,'-',true));
                // Generate the token for the confirmation email
                $conf_token = $tokenGenerator->generateToken();
                $user->setConfirmationToken($conf_token);

                //Set the roles
                $user->addRole("ROLE_ADMIN");

                $user->setFkOrganisation($organisation->getId());

                // Save it.
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                try {
                    $em->flush();
                } catch (UniqueConstraintViolationException $e) {
                    //removing the organization added before
                    $em = $this->getDoctrine()->resetManager();
                    $em->remove(
                        $em->getRepository('GrandsVoisinsBundle:Organisation')->find(
                            $organisation->getId()
                        )
                    );
                    $em->flush();
                        dump("l'utilisateur saisi est déjà présent");
                    exit;
                    //return $this->redirectToRoute('all_orga');
                }

                $organisation->setFkResponsable($user->getId());
                // tells Doctrine you want to (eventually) save the Product (no queries yet)
                $em->persist($organisation);
                try {
                    $em->flush();
                } catch (UniqueConstraintViolationException $e) {
                    //removing the organization and the user added before
                    $em = $this->getDoctrine()->resetManager();
                    $em->remove(
                        $em->getRepository('GrandsVoisinsBundle:User')->find(
                            $user->getId()
                        )
                    );
                    $em->remove(
                        $em->getRepository('GrandsVoisinsBundle:Organisation')->find(
                            $organisation->getId()
                        )
                    );
                    $em->flush();
                        dump("Problème lors de la mise à jour des champs, veuillez contacter un administrateur");

                    exit;
                    //return $this->redirectToRoute('all_orga');
                }
            }else dump($tab);
        }
        dump("ok");
        exit;
    }

}
