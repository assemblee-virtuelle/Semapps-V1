<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

// TODO Do not remove until we implement components.

class ComponentController extends Controller
{

    public function showAction()
    {
        $uri = urldecode($_POST["uri"]);
        $name = urldecode($_POST["name"]);

        $request = $this->getFormSpec($_POST["type"]);

        if (!$request){
            $this->addFlash('info','le type ne correspond à aucun formulaire');
            return $this->redirectToRoute('show_all_component');
        }

        $sfClient = $this->container->get('semantic_forms.client');
        $form = $sfClient->formData(
            $uri,
            $request
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
              'title' => 'Edition du projet : '.$name,
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
                $temp = $sfClient->sparql($project);
                $result["Project"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $title = 'Affichage de tous les Projets';
                break;
            case 'Event':
                $event = '
                prefix event: <http://purl.org/NET/c4dm/event.owl#>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a event:Event . ?URI rdfs:label ?NAME} } ';
                $temp = $sfClient->sparql($event);
                $result["Event"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $title = 'Affichage de tous les Projets';
                break;
            case 'Proposition':
                $proposition = '
                prefix fipa: <http://www.fipa.org/schemas#>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
                $temp = $sfClient->sparql($proposition);
                $result["Proposition"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $title = 'Affichage de tous les Projets';
                break;
            default:
                $project = '
                prefix foaf: <http://xmlns.com/foaf/0.1/>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
                $temp = $sfClient->sparql($project);
                $result["Project"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $event = '
                prefix event: <http://purl.org/NET/c4dm/event.owl#>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a event:Event . ?URI rdfs:label ?NAME} } ';
                $temp = $sfClient->sparql($event);
                $result["Event"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $proposition = '
                prefix fipa: <http://www.fipa.org/schemas#>
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a fipa:Proposition . ?URI rdfs:label ?NAME} } ';
                $temp = $sfClient->sparql($proposition);
                $result["Proposition"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
                $title = 'Affichage de tous les Projets, Evenements, Propositions';
        }


        return $this->render(
          'GrandsVoisinsBundle:Component:show_all.html.twig',
          array(
              'title' => $title,
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

        $request = $this->getFormSpec($type);

        if (!$request){
            $this->addFlash('info','le type ne correspond à aucun formulaire');
            return $this->redirectToRoute('show_all_component');
        }

        $json = $sfClient->createData($request);

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

    private function getFormSpec($type){
        switch ($type){
            case 'Project':
                return SemanticFormsClient::PROJET;
            case 'Event':
                return SemanticFormsClient::EVENT;
            case 'Proposition':
                return SemanticFormsClient::PROPOSITION;
            default:
                return null;
        }
    }
}
