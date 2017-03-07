<?php

namespace GrandsVoisinsBundle\Controller;


class ComponentController extends AbstractController
{
    //request type SELECT ?S WHERE { GRAPH <mailto:admin@admin.fr> {?S  ?P <http://xmlns.com/foaf/0.1/Project>   . } } LIMIT 100
    //url : http://localhost:9000/select-ui?query=SELECT+%3FS+WHERE+%7B+GRAPH+%3Cmailto%3Aadmin%40admin.fr%3E+%7B%3FS++%3FP+%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2FProject%3E+++.+%7D+%7D+LIMIT+100%0D%0A+%23+SELECT+DISTINCT+%3FCLASS+WHERE+%7B+GRAPH+%3FG+%7B+%5B%5D+a++%3FCLASS+.+%7D+%7D+LIMIT+100%0D%0A+%23+SELECT+DISTINCT+%3FPROP+WHERE+%7B+GRAPH+%3FG+%7B+%5B%5D+%3FPROP+%5B%5D+.+%7D+%7D+LIMIT+100%0D%0A
    //structure of the request : 2 steps
    //SELECT * WHERE { GRAPH <mailto:admin@admin.fr> { ?S  ?P <http://xmlns.com/foaf/0.1/Project> . } } LIMIT 100 <-- req 1
    //SELECT * WHERE { GRAPH <mailto:admin@admin.fr> { <http://localhost:9000/ldp/1488897146798-101028408537283>  ?P ?O   . } } LIMIT 100 <-- req 2

    public function showAction($uri)
    {
        $sfClient = $this->container->get('semantic_forms.client');
        //$json = $sfClient->getForm();

        return $this->render('GrandsVoisinsBundle:Component:show.html.twig', array(
            // ...
        ));
    }

    public function showAllAction($type="")
    {
        $sfClient = $this->container->get('semantic_forms.client');
        //On récupère un tableau qui viens de sparql ( tous les uri de projet event etc... ) - req1
        //Pour chaque ligne on fait une requete pour récupérer le nom du projet etc.. - req2

        return $this->render('GrandsVoisinsBundle:Component:show_all.html.twig', array(
            // ...
        ));
    }

    public function saveAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');

        $userEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:User'
        );

        $responsable = $userEntity->findOneBy(["email" => explode(':', urldecode($_POST["graphURI"]))[1]]);


        $info = $sfClient->send($_POST, $responsable->getEmail(), $responsable->getSfUser());
        if($info != 200){
            $this->addFlash(
                'success',
                'Une erreur s\'est produite. Merci de contacter l\'administrateur du site <a href="mailto:romain.weeger@wexample.com">romain.weeger@wexample.com</a>.'
            );
        }
        return $this->redirectToRoute('show_all_component');

    }

    public function newAction($type){

        $sfClient = $this->container->get('semantic_forms.client');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );

        $userEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:User'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation = $organisationEntity->find(
            $this->GetUser()->getFkOrganisation()
        );

        $responsable = $userEntity->find($organisation->getFkResponsable());

        $json = $sfClient->createFoaf($type);


        if(!$json){
            $this->addFlash('info','Une erreur s\'est produite lors de l\'affichage du formulaire');
            return $this->redirectToRoute('home');
        }

        foreach ($json["fields"] as $field) {
            $field["htmlName"] = urldecode($field["htmlName"]);
        }
        return $this->render('GrandsVoisinsBundle:Component:show.html.twig', array(
            'form' => $json,
            'graphURI' => $responsable->getGraphURI(),
            'title' => $type
        ));
    }

}
