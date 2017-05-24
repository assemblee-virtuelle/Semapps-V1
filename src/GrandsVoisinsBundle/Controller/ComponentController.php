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
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
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
        $image = $form->get('image')->getData();
        // Fill form
        return $this->render(
          'GrandsVoisinsBundle:Component:'.$this->componentName.'Form.html.twig',
          array(
            'form' => $form->createView(),
            'image' => $image
          )
        );
    }
    public function removeAction(){

        $route = [
          'project' => 'projet',
          'event' => 'evenement',
          'proposition' => 'proposition',
          ];
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        $uri = $_GET['uri'];
        $componentName = $_GET['componentName'];
        $query = "DELETE { GRAPH ?gr { <".$uri."> ?P ?O . ?S ?PP <".$uri."> .}}  WHERE {GRAPH ?gr { <".$uri."> ?P ?O . ?S ?PP <".$uri."> .}}";
        $sfClient->update($query);

        return $this->redirect('/mon-compte/'.$route[$componentName]);
    }
}
