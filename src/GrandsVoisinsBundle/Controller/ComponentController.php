<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsClient;

// TODO Do not remove until we implement components.

class ComponentController extends Controller
{
/* requete MIS A JOUR !!!!!!
prefix foaf: <http://xmlns.com/foaf/0.1/>
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
SELECT ?S ?O2 WHERE { GRAPH <urn:gv/contacts/new/row/1085-org> { ?S  ?P foaf:Project . ?S rdfs:label ?O2} }
 */
    public function showAction()
    {
        $uri = urldecode($_POST["uri"]);
        $sfClient = $this->container->get('semantic_forms.client');
        $form = $sfClient->edit(
            $uri,
            SemanticFormsClient::PROJET
        );
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation = $organisationEntity->find(
            $this->GetUser()->getFkOrganisation()
        );
        return $this->render(
          'GrandsVoisinsBundle:Component:show.html.twig',
          array(
              'title' => 'edit',
              'graphURI' => $organisation->getGraphURI(),
              'form' => $form
          )
        );
    }

    public function showAllAction($type = "")
    {
        $sfClient = $this->container->get('semantic_forms.client');
        //On récupère un tableau qui viens de sparql ( tous les uri de projet event etc... ) - req1
        //Pour chaque ligne on fait une requete pour récupérer le nom du projet etc.. - req2
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );

        $organisation = $organisationEntity->find(
            $this->getUser()->getFkOrganisation()
        );
        $result = array();
        switch ($type){
            case 'Project':
                $project = '
                prefix foaf: <http://xmlns.com/foaf/0.1/>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
                $result = $sfClient->sparql($project)["results"]["bindings"];
                break;
            default:
                $project = '
                prefix foaf: <http://xmlns.com/foaf/0.1/>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
                $result = $sfClient->sparql($project)["results"]["bindings"];
        }


        return $this->render(
          'GrandsVoisinsBundle:Component:show_all.html.twig',
          array(
              'title' => 'show all '.$type,
              'data' => $result
          )
        );
    }

    public function saveAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');

        $info = $sfClient->send(
          $_POST,
          $this->getUser()->getEmail(),
          $this->getUser()->getSfUser()
        );
        if ($info != 200) {
            $this->addFlash(
              'success',
              'Une erreur s\'est produite. Merci de contacter l\'administrateur du site <a href="mailto:romain.weeger@wexample.com">romain.weeger@wexample.com</a>.'
            );
        }

        return $this->redirectToRoute('show_all_component');

    }

    public function newAction($type)
    {

        $sfClient           = $this->container->get('semantic_forms.client');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation = $organisationEntity->find(
          $this->GetUser()->getFkOrganisation()
        );
        switch ($type){
            case 'Project':
                $json = $sfClient->create(SemanticFormsClient::PROJET);
                break;
            default:
                $this->addFlash('info','le type ne correspond à aucun formulaire');
                return $this->redirectToRoute('show_all_component');
        }

        if (!$json) {
            $this->addFlash(
              'danger',
              'Une erreur s\'est produite lors de l\'affichage du formulaire'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render(
          'GrandsVoisinsBundle:Component:show.html.twig',
          array(
            'form'     => $json,
            'graphURI' => $organisation->getGraphURI(),
            'title'    => $type,
          )
        );
    }

}
