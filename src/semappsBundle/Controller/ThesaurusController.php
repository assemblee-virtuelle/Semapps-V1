<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 02/02/18
 * Time: 14:45
 */

namespace semappsBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class ThesaurusController extends AbstractMultipleComponentController
{
    /**
     * @param $componentName
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Gère la soumission du formulaire du $componentname. Ce formulaire gère la création et la mise à jour de la donnée.
     * La gestion de l'image est géré également ici mais a son propre comportement.
     * TODO : renommer la fonction
     */
    public function addAction($componentName,Request $request)
    {
        $this->setSfLink(urldecode($request->get('uri')));
        $sfClient       = $this->get('semantic_forms.client');
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
        $contextManager = $this->get("semapps_bundle.context_manager");
        return $contextManager->getContext($this->getUser()->getSfLink())['context'];
    }

    function getSfUser($id = null)
    {
        return $this->getUser()->getEmail();
    }

    function getSfPassword($id = null)
    {
        $encryption 	= $this->get('semapps_bundle.encryption');
        return $encryption->decrypt($this->getUser()->getSfUser());
    }
    public function componentList($componentConf, $graphURI)
    {
        $sparqlrepository = $this->get('semapps_bundle.sparql_repository');
        $listOfContent = $sparqlrepository->getListOfContentByType($componentConf,$graphURI);
        return $listOfContent;
    }
    public function removeComponent($uri){
        $sfClient = $this->get('semantic_forms.client');
        $sparqlClient   = $this->get('sparqlbundle.client');

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