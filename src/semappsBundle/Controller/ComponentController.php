<?php

namespace semappsBundle\Controller;

use semappsBundle\Form\ImportType;
use Symfony\Component\HttpFoundation\Request;

class ComponentController extends AbstractMultipleComponentController
{

    public function addAction($componentName,$uri =null,Request $request)
    {
        $sparqlRepository   = $this->get('semapps_bundle.sparql_repository');
        $sfClient       = $this->get('semantic_forms.client');
        $componentConf = $this->getParameter($componentName.'Conf');
        $fileUploader = $this->get('semapps_bundle.file_uploader');
        $importManager = $this->get('semapps_bundle.import_manager');
        if($uri)
            $this->setSfLink(urldecode($uri));
        else
            $this->setSfLink(urldecode($request->get('uri')));
        $graphURI			= $this->getGraph();
        $form 				= $this->getSfForm($sfClient,$componentName, $request);

        // Remove old picture.
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
                    $this->addFlash('success', "L'image a été rajoutée avec succès");
                    return $this->redirectToRoute(
                        'componentForm', ["componentName" => $componentName, "uri" => $form->uri]
                    );
                }
            }
            if(!json_decode($form->get($componentConf['fields'][$componentConf["access"]['write']]['value'])->getData())){
                $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_INSERT_DATA);
                $sparql->addInsert('<'.$form->uri.'>','<'.$componentConf["access"]['write'].'>','<'.$this->getUser()->getSfLink().'>','<'.$graphURI.'>');
                $sfClient->update($sparql->getQuery());

            }
            $this->addFlash('success', 'Le contenu a bien été mis à jour.');
            return $this->redirectToRoute(
                'componentList', ["componentName" => $componentName]
            );
        }

        $importForm = null;
        if(!$this->getSfLink()){
            $importForm = $this->createForm(ImportType::class, null);
            $importForm->handleRequest($request);
            if ($importForm->isSubmitted() && $importForm->isValid()) {
                $uri = $importForm->get('import')->getData();

                $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_SELECT);
                $sparql->addSelect("?o")
                    ->addPrefixes($sparql->prefixes)
                    ->addWhere("<".$uri.">","rdf:type","?o","?gr")
                    ->groupBy("?o");
                $result = $sfClient->sparql($sparql->getQuery());
                if(empty($result["results"]["bindings"])){
                    $this->setSfLink($uri);

                    $componentConf = $this->getParameter($componentName.'Conf');
                    $type = array_merge([$componentConf['type']],array_key_exists('otherType',$componentConf)? $componentConf['otherType'] : []);

                    $testForm = $this->getSfForm($sfClient,$componentName, $request,$uri );
                    $dataToSave = $importManager->contentToImport($uri,$componentConf,$type);
                    //dump($dataToSave);exit;
                    if(is_null($dataToSave)){
                        $this->setSfLink(null);
                        $this->addFlash("info","L'URI renseignée ne renvoie aucune donnée");

                    }elseif(!$dataToSave){
                        $this->setSfLink(null);
                        $this->addFlash("info","L'URI renseignée ne correspond pas au type d'entité que vous avez sélectionné");

                    }else{
                        $this->addFlash("success","Le profil a été importé avec succès !");
//                        dump($dataToSave);exit;
                        $testForm->submit($dataToSave);
                        return $this->redirectToRoute('componentForm', ["componentName" => $componentName, "uri" => urlencode($uri)]);
                    }
                }else{
                    $this->addFlash("info","L'URI existe déjà");
                }
            }
        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($componentName).':'.$componentName.'Form.html.twig',
            [
                'image' => $actualImageName,
                'form' => $form->createView(),
                "entityUri" => $this->getSfLink(),
                'importForm'=>  ($importForm)?$importForm->createView():null,
                'componentName' => $componentName
            ]
        );

    }

    public function actualizeAction(Request $request,$componentName,$uri =null){
        $uri = urldecode($uri);
        $sfClient =$this->get('semantic_forms.client');
        $importManager = $this->get('semapps_bundle.import_manager');
        if( $uri ){
            $componentConf = $this->getParameter($componentName.'Conf');
            $this->setSfLink($uri);
            $testForm = $this->getSfForm($sfClient,$componentName, $request,$uri );
            $type = array_merge([$componentConf['type']],array_key_exists('otherType',$componentConf)? $componentConf['otherType'] : []);
            $dataToSave = $importManager->contentToImport($uri,$componentConf,$type);
            $testForm->submit($dataToSave,false);
            $this->addFlash('success','Actualisation ok !');
        }else{
            $this->addFlash('info',"Problème lors de l'actualisation !");
        }
        return $this->redirectToRoute('componentFormWithUri',["componentName" => $componentName,"uri" => urlencode($uri)]);
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
//        $listOfContent = $sparqlrepository->getListOfContentByType($componentConf,null);
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
