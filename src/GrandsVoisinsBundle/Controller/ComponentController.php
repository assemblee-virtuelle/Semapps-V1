<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ComponentController extends Controller
{

    var $componentName = 'undefined';
    var $pluralName = 'undefined';
    var $sparqlPrefix = 'undefined';

    public function listAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );

        $organisation = $organisationEntity->find(
          $this->getUser()->getFkOrganisation()
        );

        $results = $sfClient->sparql(
          $sfClient->prefixesCompiled.
          'SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI(
          ).'> { ?URI a '.$this->sparqlPrefix.' . ?URI rdfs:label ?NAME} } '
        );

        $listContent = [];
        if (isset($results["results"]["bindings"])) {
            foreach ($results["results"]["bindings"] as $item) {
                $listContent[] = [
                  'uri'   => $item['URI']['value'],
                  'title' => $item['NAME']['value'],
                ];
            }
        }

//        echo $request;
//        exit;

//        $result = array();
//        switch ($type){
//            case 'Project':

//            case 'Event':
//                $event = '
//                prefix event: <http://purl.org/NET/c4dm/event.owl#>
//                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
//                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
//                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a event:Event . ?URI rdfs:label ?NAME} } ';
//                $temp = $sfClient->sparql($event);
//                $result["Event"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
//                $title = 'Affichage de tous les Projets';
//                break;
//            case 'Proposition':
//                $proposition = '
//                prefix fipa: <http://www.fipa.org/schemas#>
//                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
//                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
//                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
//                $temp = $sfClient->sparql($proposition);
//                $result["Proposition"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
//                $title = 'Affichage de tous les Projets';
//                break;
//            default:
//                $project = '
//                prefix foaf: <http://xmlns.com/foaf/0.1/>
//                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
//                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
//                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a foaf:Project . ?URI rdfs:label ?NAME} } ';
//                $temp = $sfClient->sparql($project);
//                $result["Project"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
//                $event = '
//                prefix event: <http://purl.org/NET/c4dm/event.owl#>
//                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
//                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
//                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a event:Event . ?URI rdfs:label ?NAME} } ';
//                $temp = $sfClient->sparql($event);
//                $result["Event"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
//                $proposition = '
//                prefix fipa: <http://www.fipa.org/schemas#>
//                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
//                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
//                SELECT ?URI ?NAME WHERE { GRAPH <'.$organisation->getGraphURI().'> { ?URI a fipa:Proposition . ?URI rdfs:label ?NAME} } ';
//                $temp = $sfClient->sparql($proposition);
//                $result["Proposition"] = (is_array($temp)) ? $temp["results"]["bindings"] : null;
//                $title = 'Affichage de tous les Projets, Evenements, Propositions';
//        }

        return $this->render(
          'GrandsVoisinsBundle:Component:'.$this->componentName.'List.html.twig',
          array(
            'componentName' => $this->componentName,
            'plural'        => $this->pluralName,
            'listContent'   => $listContent,
          )
        );
    }

    public function addAction(Request $request)
    {
        /** @var $user \GrandsVoisinsBundle\Entity\User */
        $user         = $this->getUser();
        $sfClient     = $this->container->get('semantic_forms.client');
        $organisation = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:Organisation')
          ->find($this->getUser()->getFkOrganisation());

        // Same as FormType::class
        $componentClassName = 'GrandsVoisinsBundle\Form\\'.ucfirst(
            $this->componentName
          ).'Type';

        $specName = 'SPEC_'.strtoupper($request->get('component'));

        $form = $this->createForm(
          $componentClassName,
          null,
          [
            'login'                 => $user->getEmail(),
            'password'              => $user->getSfUser(),
            'graphURI'              => $organisation->getGraphURI(),
            'client'                => $sfClient,
            'spec'                  => constant(
              'VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient::'.$specName
            ),
            'values'                => $request->get('uri'),
            'lookupUrlLabel'        => $this->generateUrl(
              'webserviceFieldUriLabel'
            ),
            'lookupUrlPerson'       => $this->generateUrl(
              'webserviceFieldUriSearch'
            ),
            'lookupUrlOrganization' => $this->generateUrl(
              'webserviceFieldUriSearch'
            ),
          ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('info', 'Le contenu à bien été mis à jour.');

            return $this->redirectToRoute(
              strtolower($request->get('component')).'List'
            );
        }

        // Fill form
        return $this->render(
          'GrandsVoisinsBundle:Component:'.$this->componentName.'Form.html.twig',
          array(
            'form' => $form->createView(),
          )
        );
    }

//
//    private function getFormSpec($type){
//        switch ($type){
//            case 'Project':
//                return SemanticFormsClient::PROJET;
//            case 'Event':
//                return SemanticFormsClient::EVENT;
//            case 'Proposition':
//                return SemanticFormsClient::PROPOSITION;
//            default:
//                return null;
//        }
//    }
}
