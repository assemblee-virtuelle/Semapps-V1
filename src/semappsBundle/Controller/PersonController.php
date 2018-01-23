<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Util\TokenGenerator;
use semappsBundle\Entity\User;
use semappsBundle\Form\ImportType;
use semappsBundle\Form\RegisterType;
use semappsBundle\Form\UserType;
use semappsBundle\Repository\UserRepository;
use semappsBundle\Form\AdminSettings;
use semappsBundle\semappsConfig;
use semappsBundle\Services\contextManager;
use semappsBundle\Services\SparqlRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use VirtualAssembly\SparqlBundle\Sparql\sparqlSelect;

class PersonController extends UniqueComponentController
{

    public function homeAction()
    {
        return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => "person"]);
    }

    public function addAction($uniqueComponentName,$id =null,Request $request)
    {
        /** @var SemanticFormsClient $sfClient */
        $sfClient       = $this->container->get('semantic_forms.client');
        /** @var $user \semappsBundle\Entity\User */
        $user           = $this->getElement($id);
        $userSfLink     = $user->getSfLink();
        /** @var SparqlRepository $sparqlRepository */
        $sparqlRepository   = $this->container->get('semappsBundle.sparqlRepository');
        /** @var \semappsBundle\Services\contextManager $contextManager */
        $contextManager   = $this->container->get('semappsBundle.contextManager');

        $contextManager->actualizeContext($this->getUser()->getSfLink());

        $em = $this->getDoctrine()->getManager();
        $oldPictureName = $user->getPictureName();
        /** @var Form $form */
        $form = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Manage picture.
            $newPicture = $form->get('pictureName')->getData();
            if ($newPicture) {
                // Remove old picture.
                $fileUploader = $this->get('semappsBundle.fileUploader');
                if ($oldPictureName) {
                    $dir = $fileUploader->getTargetDir();
                    // Check if file exists to avoid all errors.
                    if (is_file($dir.'/'.$oldPictureName)) {
                        $fileUploader->remove($oldPictureName);
                    }
                }
                $user->setPictureName(
                    $fileUploader->upload($newPicture)
                );
                $sparqlRepository->changeImage($form->uri,$form->uri,$fileUploader->generateUrlForFile($user->getPictureName()));

            } else {
                $user->setPictureName($oldPictureName);
            }
            // User never had a sf link, so save it.
            if (!$userSfLink) {
                // Update sfLink.
                $user->setSfLink($form->uri);
            }
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre profil a bien été mis à jour.'
            );
            if(!$id)
                return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);
            else
                return $this->redirectToRoute('personComponentForm',['uniqueComponentName' => $uniqueComponentName,'id' => $id]);
        }
        // import
        $importForm = null;
        if(!$userSfLink){
            $importForm = $this->createForm(ImportType::class, null);
            $importForm->handleRequest($request);

            if ($importForm->isSubmitted() && $importForm->isValid()) {
                $uri = $importForm->get('import')->getData();
                $user->setSfLink($uri);
                $em->persist($user);
                $em->flush();
                $contextManager->setContext($uri,null);
                //importer le profile
                //$sfClient->import($uri);
                $testForm = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );
                $contentToImport = json_decode(file_get_contents($uri),JSON_OBJECT_AS_ARRAY);
                $componentConf = $this->getParameter($uniqueComponentName.'Conf');

                $contentforForm = [
                    'type' => semappsConfig::URI_PAIR_PERSON
                ];
                foreach ($contentToImport['@context'] as $localHtmlName => $content){
                    if(array_key_exists($content['@id'],$componentConf['fields'])){
                        if (is_array($contentToImport[$localHtmlName])){
                            if(array_key_exists('@value',$contentToImport[$localHtmlName])){
                                $contentforForm[$componentConf['fields'][$content['@id']]['value']] = $contentToImport[$localHtmlName]['@value'];
                            }else{
                                $contentforForm[$componentConf['fields'][$content['@id']]['value']] = json_encode(array_flip($contentToImport[$localHtmlName]));
                            }
                        }else{
                            $contentforForm[$componentConf['fields'][$content['@id']]['value']] = $contentToImport[$localHtmlName];
                        }
                    }
                }
                $testForm->submit($contentforForm);

                if(!$id)
                    return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);
                else
                    return $this->redirectToRoute('personComponentForm',['uniqueComponentName' => $uniqueComponentName,'id' => $id]);
            }
        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($uniqueComponentName).':'.$uniqueComponentName.'Form.html.twig',[
                'importForm'=> ($importForm != null)? $importForm->createView() : null,
                "form" => $form->createView(),
                "entityUri" => $this->getSfLink($id),
                'currentUser' => $user
            ]
        );
    }

    public function actualizeAction(Request $request,$uniqueComponentName,$id =null){
        $user = $this->getElement($id);
        $sfClient =$this->container->get('semantic_forms.client');
        $importManager = $this->container->get('semappsBundle.importmanager');
        if($user->getSfLink() ){
            //$importManager->actualize($user->getSfLink());
            $contentToImport = json_decode(file_get_contents($user->getSfLink()),JSON_OBJECT_AS_ARRAY);
            $componentConf = $this->getParameter($uniqueComponentName.'Conf');

            $importManager->removeUri($user->getSfLink());
            /** @var Form $testForm */
            $testForm = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );

            $contentforForm = [
                'type' => semappsConfig::URI_PAIR_PERSON
            ];
            foreach ($contentToImport['@context'] as $localHtmlName => $content){
                if(array_key_exists($content['@id'],$componentConf['fields'])){
                    if (is_array($contentToImport[$localHtmlName])){
                        if(array_key_exists('@value',$contentToImport[$localHtmlName])){
                            $contentforForm[$componentConf['fields'][$content['@id']]['value']] = $contentToImport[$localHtmlName]['@value'];
                        }else{
                            $contentforForm[$componentConf['fields'][$content['@id']]['value']] = json_encode(array_flip($contentToImport[$localHtmlName]));
                        }
                    }else{
                        $contentforForm[$componentConf['fields'][$content['@id']]['value']] = $contentToImport[$localHtmlName];
                    }
                }
            }
            //dump($contentforForm);
            $testForm->submit($contentforForm);
            $this->addFlash('success','ok');
        }else{
            $this->addFlash('success','NOK !!!');

        }

        return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);

    }

    public function removeAction($uniqueComponentName, $id =null){
        $user = $this->getElement($id);

        /** @var \semappsBundle\Services\ImportManager $importManager */
        $importManager = $this->container->get('semappsBundle.importmanager');
        if($user->getSfLink()){
            $importManager->removeUri($user->getSfLink());
            $user->setSfLink(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            try {
                $em->flush();
                $this->addFlash('success','ok');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "Problème mise à jour");
                return $this->redirectToRoute('personComponentFormWithoutId',['uniqueComponentName' => $uniqueComponentName]);

            }
        }else{
            $this->addFlash('success','NOK !!!');
        }

        return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);
    }


    public function getElement($id =null)
    {
        $userManager         = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'semappsBundle:User'
            );
        if ($id){
            return $userManager->find($id);
        }
        else{
            return $this->getUser();

        }
    }

    public function getSfLink($id = null)
    {
        if ($id){
            return $this->getElement($id)->getSfLink();
        }
        else{
            return $this->getUser()->getSfLink();
        }
    }

    public function getGraph($id = null)
    {
        return $this->getSfLink($id);
    }

}
