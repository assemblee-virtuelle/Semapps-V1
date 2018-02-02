<?php

namespace semappsBundle\Controller;

use semappsBundle\Form\ImportType;
use semappsBundle\Services\contextManager;
use semappsBundle\Services\ImportManager;
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
            $newPictureName = null;
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
                    $this->addFlash(
                        'success',
                        'Votre image a bien été changé.'
                    );
                }

            }
            if(!$newPictureName)
                $this->addFlash(
                    'success',
                    'Le contenu a bien été mis à jour.'
                );

            return $this->redirectToRoute('orgaComponentForm',['uniqueComponentName' => $componentName, 'id' =>urlencode($form->uri)]);

        }

        $importForm = null;
        if(!$this->getSfLink()){
            $importForm = $this->createForm(ImportType::class, null);
            $importForm->handleRequest($request);
            /** @var ImportManager $importManager */
            $importManager = $this->container->get('semappsBundle.importmanager');
            if ($importForm->isSubmitted() && $importForm->isValid()) {
                $uri = $importForm->get('import')->getData();

                $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_SELECT);
                $sparql->addSelect("?o")
                    ->addPrefixes($sparql->prefixes)
                    ->addWhere("<".$uri.">","rdf:type","?o","?gr")
                    ->groupBy("?o");
                $result = $sfClient->sparql($sparql->getQuery());
                $contextManager->setContext($uri,null);
                if(empty($result["results"]["bindings"])){
                    $this->setSfLink($uri);

                    $componentConf = $this->getParameter($componentName.'Conf');
                    $type = array_merge([$componentConf['type']],array_key_exists('otherType',$componentConf)? $componentConf['otherType'] : []);

                    $testForm = $this->getSfForm($sfClient,$componentName, $request,$uri );
                    $dataToSave = $importManager->contentToImport($uri,$componentConf['fields'],$type);
                    //dump($dataToSave);exit;
                    if(is_null($dataToSave)){
                        $this->setSfLink(null);
                        $this->addFlash("info","l'Uri donnée ne renvoie aucune donnée");

                    }elseif(!$dataToSave){
                        $this->setSfLink(null);
                        $this->addFlash("info","l'uri donnée ne correspond pas au type actuel");

                    }else{
                        $this->addFlash("success","Le profil organisation a été importé avec succès !");
                        if(array_key_exists('hasResponsible',$dataToSave)){
                            $hasResponsible = json_decode($dataToSave['hasResponsible'],JSON_OBJECT_AS_ARRAY);
                            $hasResponsible[$this->getUser()->getSfLink()] = 0;
                            $dataToSave['hasResponsible'] = json_encode($hasResponsible);
                        }
                        else{
                            $hasResponsible[$this->getUser()->getSflink()] = 0;
                            $dataToSave['hasResponsible'] = json_encode($hasResponsible);
                        }
//                    dump($dataToSave);exit;
                        $testForm->submit($dataToSave);
                        return $this->redirectToRoute('orgaComponentForm',['uniqueComponentName' => $componentName, 'id' => urlencode($uri)]);
                    }
                }else{
                    $this->addFlash("info","l'Uri existe déjà");
                }
            }
        }

        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($componentName).':'.$componentName.'Form.html.twig',[
                'importForm'=>  ($importForm)?$importForm->createView():null,
                "form" => $form->createView(),
                "entityUri" => $this->getSfLink(),
                "image" => $actualImageName
            ]
        );
    }

    public function actualizeAction(Request $request,$uniqueComponentName,$id =null){
        $uri = urldecode($id);
        $sfClient =$this->container->get('semantic_forms.client');
        /** @var ImportManager $importManager */
        $importManager = $this->container->get('semappsBundle.importmanager');
        if( $uri ){
            $componentConf = $this->getParameter($uniqueComponentName.'Conf');
            $testForm = $this->getSfForm($sfClient,$uniqueComponentName, $request,$uri );
            $type = array_merge([$componentConf['type']],array_key_exists('otherType',$componentConf)? $componentConf['otherType'] : []);
            $dataToSave = $importManager->contentToImport($uri,$componentConf['fields'],$type);
            $testForm->submit($dataToSave,false);
            $this->addFlash('success','Actualisation ok !');
        }else{
            $this->addFlash('info',"Problème lors de l'actualisation !");
        }
        return $this->redirectToRoute('orgaComponentForm',["uniqueComponentName" => $uniqueComponentName,"id" => urlencode($uri)]);
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
