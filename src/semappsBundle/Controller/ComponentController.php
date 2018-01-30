<?php

namespace semappsBundle\Controller;

use semappsBundle\Services\contextManager;
use semappsBundle\Services\SparqlRepository;
use Symfony\Component\HttpFoundation\Request;

class ComponentController extends AbstractMultipleComponentController
{

    public function addAction($componentName,Request $request)
    {
        /** @var SparqlRepository $sparqlRepository */
        $sparqlRepository   = $this->container->get('semappsBundle.sparqlRepository');
        $this->setSfLink($request->get('uri'));
        $graphURI			= $this->getGraph();
        $sfClient       = $this->container->get('semantic_forms.client');
        $form 				= $this->getSfForm($sfClient,$componentName, $request);

        // Remove old picture.
        $fileUploader = $this->get('semappsBundle.fileUploader');
        $pictureDir = $fileUploader->getTargetDir();
        //actualPicture
        $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_SELECT);
        $sparql->addPrefixes($sparql->prefixes)
            ->addPrefix('pair', 'http://virtual-assembly.org/pair#')
            ->addSelect('?oldImage')
            ->addWhere(
                $sparql->formatValue($this->getSfLink(), $sparql::VALUE_TYPE_URL),
                'pair:image',
                '?oldImage',
                $sparql->formatValue($graphURI, $sparql::VALUE_TYPE_URL));
        $results = $sfClient->sparql($sparql->getQuery());
        $actualImage = $sfClient->sparqlResultsValues($results);
        $actualImageName = null;
        if (!empty($actualImage)) {
            $cutUrl = explode("/", $actualImage[0]['oldImage']);
            $actualImageName = $cutUrl[sizeof($cutUrl) - 1];
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Manage picture.
            if($form->has('componentPicture')){
                $newPicture = $form->get('componentPicture')->getData();
                if ($newPicture) {

                    if ($actualImageName) {
                        // Check if file exists to avoid all errors.
                        if (is_file($pictureDir . '/' . $actualImageName)) {
                            $fileUploader->remove($actualImageName);
                        }
                    }
                    $newPictureName = $fileUploader->upload($newPicture);
                    $sparqlRepository->changeImage($graphURI,$form->uri,$fileUploader->generateUrlForFile($newPictureName));
                    $this->addFlash('success', "l'image a été rajouté avec succès");
                    return $this->redirectToRoute(
                        'componentForm', ["componentName" => $componentName, "uri" => $form->uri]
                    );
                }
            }
            $this->addFlash('success', 'Le contenu à bien été mis à jour.');
            return $this->redirectToRoute(
                'componentList', ["componentName" => $componentName]
            );
        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($componentName).':'.$componentName.'Form.html.twig',
            ['image' => $actualImageName,
                'form' => $form->createView()
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
