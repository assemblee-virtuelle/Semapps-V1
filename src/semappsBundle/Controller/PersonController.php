<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Form\ImportType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class PersonController extends UniqueComponentController
{

    public function homeAction()
    {
        return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => "person"]);
    }

    /**
     * @param $uniqueComponentName
     * @param null $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Gère la soumission du formulaire person. Ce formulaire gère la création et la mise à jour de la donnée.
     * La gestion de l'image est géré également ici mais a son propre comportement.
     * le côté spécifique de cette fonction est que elle est en lien aussi avec la base de donnée SQL pour gérer le fait que un compte = un profil
     * TODO : renommer la fonction
     */
    public function addAction($uniqueComponentName,$id =null,Request $request)
    {
        $sfClient       = $this->get('semantic_forms.client');
        $user           = $this->getElement($id);
        $userSfLink     = $user->getSfLink();
        $sparqlRepository   = $this->get('semapps_bundle.sparql_repository');
        $contextManager   = $this->get('semapps_bundle.context_manager');

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:User');

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
                $fileUploader = $this->get('semapps_bundle.file_uploader');
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
                $this->addFlash(
                    'success',
                    'Votre image a bien été changée.'
                );
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
            if(!$newPicture){
                $this->addFlash(
                    'success',
                    'Votre profil a bien été mis à jour.'
                );
            }

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
            $importManager = $this->get('semapps_bundle.import_manager');
            if ($importForm->isSubmitted() && $importForm->isValid()) {
                $uri = $importForm->get('import')->getData();
                $result = $userRepository->findOneBy(['sfLink' =>$uri]);
                if(!$result){
                    $user->setSfLink($uri);
                    $em->persist($user);
                    $em->flush();
                    $componentConf = $this->getParameter($uniqueComponentName.'Conf');
                    $testForm = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );
                    $type = array_merge([$componentConf['type']],$componentConf['otherType']);
                    $dataToSave = $importManager->contentToImport($uri,$componentConf,$type);

                    if(is_null($dataToSave)){
                        $this->addFlash("info","L'URI renseignée ne renvoie aucune donnée");

                    }elseif(!$dataToSave){
                        $this->addFlash("info","L'URI renseignée ne correspond pas au type d'entité que vous avez sélectionné");

                    }else{
                        $this->addFlash("success","Votre profil a été importé avec succès !");
                        $testForm->submit($dataToSave);

                        $contextManager->setContext($uri,null);
                    }

                }else{
                    $this->addFlash("info","L'URI existe déjà");
                }

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

    /**
     * @param Request $request
     * @param $uniqueComponentName
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Pour ceux qui ont importé leur profil, Actualise le contenu par rapport à la source.
     */
    public function actualizeAction(Request $request,$uniqueComponentName,$id =null){
        $user = $this->getElement($id);
        $sfClient =$this->get('semantic_forms.client');
        $importManager = $this->get('semapps_bundle.import_manager');
        if($user->getSfLink() ){
            $componentConf = $this->getParameter($uniqueComponentName.'Conf');
            $testForm = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );
            $type = array_merge([$componentConf['type']],$componentConf['otherType']);
            $dataToSave = $importManager->contentToImport($user->getSfLink(),$componentConf,$type);
            $testForm->submit($dataToSave,false);
            $this->addFlash('success','Actualisation ok !');
        }else{
            $this->addFlash('info',"Problème lors de l'actualisation !");

        }

        return $this->redirectToRoute('personComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);

    }

    /**
     * @param $uniqueComponentName
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Supprime le profil d'une personne dans la base SPARQL et SQL
     */
    public function removeAction($uniqueComponentName, $id =null){
        $user = $this->getElement($id);

        $importManager = $this->get('semapps_bundle.import_manager');
        if($user->getSfLink()){
            $importManager->removeUri($user->getSfLink());
            $user->setSfLink(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            try {
                $em->flush();
                $this->addFlash('success',"Le profil de l'utilisateur a été supprimé avec succès");
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "Problème lors de la suppression du profil");
                return $this->redirectToRoute('personComponentFormWithoutId',['uniqueComponentName' => $uniqueComponentName]);

            }
        }else{
            $this->addFlash('success',"L'utilisateur courant n'a pas d'URI");
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
