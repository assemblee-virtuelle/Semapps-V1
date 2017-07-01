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
                                ?S <http://xmlns.com/foaf/0.1/name> ?O.
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
                                ?S <http://www.w3.org/ns/org#Head> ?O.
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
        $noThisOrga= ["urn:gv/contacts/row/24-org"];
        dump("RESULTAT REQUETE");
        dump("nomRes");
        dump($nomRes);
        dump("responsableRes");
        dump($responsableRes);
        dump("batimentResE");
        dump($batimentRes);
        dump("TRANSFORMATION");
        foreach ($responsableRes as $nom){
            if(!in_array($nom["G"]["value"],$noThisOrga))
                $tab[$nom["G"]["value"] ] = array($nom["O"]["value"]);
        }
        dump("apres avoir placer les responsables");
        dump($tab);
        $i=0;
        foreach ($nomRes as $nom){
            if(!in_array($nom["G"]["value"],$noThisOrga))
                array_push($tab[$nom["G"]["value"]],(!$nom["O"]["value"])? "nom".$i."_NULL" : $nom["O"]["value"]);
            ++$i;
        }
        dump("apres avoir placer les nom des orga");
        dump($tab);
        foreach ($batimentRes as $batiment){
            if(!in_array($batiment["G"]["value"],$noThisOrga))
                array_push($tab[$batiment["G"]["value"]],(!$batiment["O"]["value"])? "batiment_NULL" : $batiment["O"]["value"]);
        }
        dump("apres avoir placer les nom des batiments");
        dump($tab);
        $email = array();
        $email2 = array();
        foreach ($tab as $key => $value){
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
                $tab[$key][0] = $personneRes;
                $email2[$key] = $tab[$key];
                unset($tab[$key]);

            }
            else {
                array_push($email,$personneRes);
                $tab[$key][0] = $personneRes;
            }


        }
        dump($email2);
        dump($tab);

        $orga = array();
        $em = $this->getDoctrine()->getManager();
        $orgaRepository = $em->getRepository('GrandsVoisinsBundle:Organisation');
        $i =0;

        foreach ($tab as $key=>$val){
            set_time_limit ( 30 );

            if(!in_array($val[1],$orga)){
                $organisation = new Organisation();
                $user = new User();
                //for the organisation
                $organisation->setName($val[1]);
                array_push($orga,$val[1]);
                $organisation->setSfOrganisation($key);
                // tells Doctrine you want to (eventually) save the Product (no queries yet)

                $organisation->setGraphURI(
                    $key
                );
                $em->persist($organisation);
                // actually executes the queries (i.e. the INSERT query)
                try {
                    $em->flush($organisation);
                } catch (UniqueConstraintViolationException $e) {
                    dump($key,$val);
                    dump("le nom de l'orgnanisation que vous avez saisi est déjà présent");
                    exit;
                    //return $this->redirectToRoute('all_orga');
                }
                //TODO find a way to call teamAction in admin
                //for the user
                $user->setUsername(stristr($val[0],'@',true).$i);
                $i++;
                $user->setEmail($val[0]);
                /** @var \GrandsVoisinsBundle\Services\Encryption $encryption */
                $encryption = $this->container->get('GrandsVoisinsBundle.encryption');
                // Generate password.
                $tokenGenerator = $this->container->get(
                    'fos_user.util.token_generator'
                );
                $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
                $user->setPassword(
                    password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
                );

                $user->setSfUser($encryption->encrypt($randomPassword));
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
            }else dump($val);
        }
        dump("ok");

        exit;
    }

    public function updatePasswordAction(){
        dump("*** UPDATE PASSWORD ***");
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('GrandsVoisinsBundle:User');
        /** @var \GrandsVoisinsBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('GrandsVoisinsBundle.encryption');
        $allUser = $userRepository->findAll();

        foreach ($allUser as $user){
            $oldPassword = $user->getSfUser();
            $newPassword = $encryption->encrypt($oldPassword);
            $newPasswordDecrypted = $encryption->decrypt($newPassword);
            if($oldPassword == $newPasswordDecrypted){
                $user->setSfUser($newPassword);
                $em->persist($user);
                $em->flush();
                $userTest = $userRepository->find($user->getId());
                if($encryption->decrypt($userTest->getSfUser()) != $oldPassword){
                    dump('id:'.$userTest->getId());
                    dump('oldpassword:'.$oldPassword);
                    dump('password decrypted:'.$encryption->decrypt($userTest->getSfUser()));
                    exit;
                }
            }
            else{
                dump('NOK');
                dump('old:'.$oldPassword);
                dump('crypt:'.$newPassword);
                dump('new:'.$newPasswordDecrypted);
                exit;
            }
            dump('OK pour id:'.$user->getId());
        }
        dump("tout est ok !");
        exit;
    }

}