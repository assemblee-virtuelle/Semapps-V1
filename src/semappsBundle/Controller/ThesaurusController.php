<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 02/02/18
 * Time: 14:45
 */

namespace semappsBundle\Controller;


//use semappsBundle\Form\ImportType;
use semappsBundle\Services\contextManager;
//use semappsBundle\Services\ImportManager;
use semappsBundle\Services\SparqlRepository;
use Symfony\Component\HttpFoundation\Request;

class ThesaurusController extends AbstractMultipleComponentController
{

    public function addAction($componentName,Request $request)
    {
        /** @var SparqlRepository $sparqlRepository */
        $this->setSfLink(urldecode($request->get('uri')));
        $sfClient       = $this->container->get('semantic_forms.client');
        $form 				= $this->getSfForm($sfClient,$componentName, $request);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Le contenu à bien été mis à jour.');
            return $this->redirectToRoute(
                'componentList', ["componentName" => $componentName]
            );
        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($componentName).':'.$componentName.'Form.html.twig',
            [
                'form' => $form->createView(),
                "entityUri" => $this->getSfLink(),
            ]
        );

    }

    function getGraph($id = null)
    {
        /** @var contextManager $contextManager */
        $contextManager = $this->container->get("semappsBundle.contextManager");
        return $contextManager->getContext($this->getUser()->getSfLink())['context'];
    }

    function getSfUser($id = null)
    {
        return $this->getUser()->getEmail();
    }

    function getSfPassword($id = null)
    {
        /** @var \semappsBundle\Services\Encryption $encryption */
        $encryption 	= $this->container->get('semappsBundle.encryption');
        return $encryption->decrypt($this->getUser()->getSfUser());
    }
    public function componentList($componentConf, $componentType, $graphURI)
    {
        /** @var SparqlRepository $sparqlrepository */
        $sparqlrepository = $this->container->get('semappsBundle.sparqlRepository');
        $listOfContent = $sparqlrepository->getListOfContentByType($componentType,$componentConf,$graphURI);
        return $listOfContent;
    }
    public function removeComponent($uri){
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
        $sparqlDeux = clone $sparql;

        $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

        $sparql->addDelete($uri,'?P','?O','?gr')
            ->addWhere($uri,'?P','?O','?gr');
        $sparqlDeux->addDelete('?s','?PP',$uri,'?gr')
            ->addWhere('?s','?PP',$uri,'?gr');

        $sfClient->update($sparql->getQuery());
        $sfClient->update($sparqlDeux->getQuery());
    }

}