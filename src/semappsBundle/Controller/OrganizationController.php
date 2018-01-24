<?php

namespace semappsBundle\Controller;

use semappsBundle\Services\contextManager;
use semappsBundle\Services\SparqlRepository;
use Symfony\Component\HttpFoundation\Request;

class OrganizationController extends AbstractMultipleComponentController
{


    public function addAction($componentName ="organization",$id = null,Request $request)
    {
        $uri = urldecode($id);
        //voter
        /** @var SparqlRepository $sparqlRepository */
        $sparqlRepository   = $this->container->get('semappsBundle.sparqlRepository');
        $this->setSfLink($uri);
        $graphURI			= $this->getGraph();
        $sfClient       = $this->container->get('semantic_forms.client');
        /** @var contextManager $contextManager */
        $contextManager       = $this->container->get('semappsBundle.contextManager');
        $form 				= $this->getSfForm($sfClient,$componentName, $request);

        if($uri && !$contextManager->actualizeContext($this->getUser()->getSfLink())){
            return $this->redirectToRoute('personComponentFormWithoutId',['uniqueComponentName' => 'person']);
        }

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
            if($form->has('organisationPicture')){
                $newPicture = $form->get('organisationPicture')->getData();
                if ($newPicture) {

                    if ($actualImageName) {
                        // Check if file exists to avoid all errors.
                        if (is_file($pictureDir . '/' . $actualImageName)) {
                            $fileUploader->remove($actualImageName);
                        }
                    }
                    $newPictureName = $fileUploader->upload($newPicture);
                    if($uri)
                        $sparqlRepository->changeImage($graphURI,$uri,$fileUploader->generateUrlForFile($newPictureName));
                    $actualImageName = $newPictureName;
                }
                $this->addFlash('info', "l'image a été rajouté avec succès");

            }
            $this->addFlash('info', 'Le contenu à bien été mis à jour.');
            return $this->redirectToRoute('orgaComponentForm',['uniqueComponentName' => $componentName, 'id' =>urlencode($form->uri)]);

        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($componentName).':'.$componentName.'Form.html.twig',[
                'importForm'=>  null,
                "form" => $form->createView(),
                "entityUri" => $this->getSfLink(),
                "image" => $actualImageName
            ]
        );

    }

    public function getGraph($id = null)
    {
        return $this->getSfLink();

    }

    public function getSfUser($id = null)
    {
        return $this->getUser()->getEmail();
    }

    public function getSfPassword($id = null)
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
