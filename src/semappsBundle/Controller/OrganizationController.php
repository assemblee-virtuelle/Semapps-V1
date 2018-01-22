<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Entity\Organization;
use semappsBundle\Entity\User;
use semappsBundle\Form\OrganisationMemberType;
use semappsBundle\semappsConfig;
use semappsBundle\Services\contextManager;
use semappsBundle\Services\SparqlRepository;
use SimpleExcel\SimpleExcel;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrganizationController extends UniqueComponentController
{


    public function addAction($uniqueComponentName,$id =null,Request $request)
    {
        $sfClient       = $this->container->get('semantic_forms.client');
        $organization = $this->getOrga($id);
        /** @var SparqlRepository $sparqlRepository */
        $sparqlRepository   = $this->container->get('semappsBundle.sparqlRepository');
        $em = $this->getDoctrine()->getManager();
        $sfLink = $this->getSfLink($id);
        $oldPictureName = $organization->getOrganisationPicture();
        /** @var Form $form */
        $form = $this->getSfForm($sfClient,$uniqueComponentName, $request,$id );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Manage picture.
            $newPicture = $form->get('organisationPicture')->getData();
            if ($newPicture) {
                // Remove old picture.
                $fileUploader = $this->get('semappsBundle.fileUploader');
                if ($oldPictureName) {
                    $oldDir = $fileUploader->getTargetDir();
                    // Check if file exists to avoid all errors.
                    if (is_file($oldDir.'/'.$oldPictureName)) {
                        $fileUploader->remove($oldPictureName);
                    }
                }
                $organization->setOrganisationPicture(
                    $fileUploader->upload($newPicture)
                );
                $sparqlRepository->changeImage($organization->getGraphURI(),$sfLink,$fileUploader->generateUrlForFile($organization->getOrganisationPicture()));
            } else {
                $organization->setOrganisationPicture($oldPictureName);
            }

            if (!$sfLink) {
                // Update sfOrganisation.
                $organization->setSfOrganisation($form->uri);
            }
            $em->persist($organization);
            $em->flush();

            $this->addFlash(
                'success',
                'Les données de l\'organisation ont bien été mises à jour.'
            );
            if(!$id)
                return $this->redirectToRoute('orgaComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);
            else
                return $this->redirectToRoute('orgaComponentForm',['uniqueComponentName' => $uniqueComponentName,'id' => $id]);
        }

        $importForm = null;
        if(!$sfLink){
            $importForm = $this->createFormBuilder();
            $importForm->add('import',UrlType::class);
            $importForm->add('save',SubmitType::class);
            $importForm = $importForm->getForm();
            $importForm->handleRequest($request);

            if ($importForm->isSubmitted() && $importForm->isValid()) {
                $uri = $importForm->get('import')->getData();
                $organization->setSfOrganisation($uri);
                $em->persist($organization);
                $em->flush();
                //importer le profil
                $sfClient->import($uri);
                //déplacer dans le graph de l'orga
                $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_INSERT);
                $graphFormatted = $sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL);

                $sparql->addPrefixes($sparql->prefixes)
                    ->addPrefix('pair','http://virtual-assembly.org/pair#');
                //$sparql->addDelete("?s","?p","?o",$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL));
                $sparql->addWhere("?s","?p","?o",$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL));
                $sparql->addInsert("?s","?p","?o",$graphFormatted);
                //dump($sparql->getQuery());
                $sfClient->update($sparql->getQuery());

                if(!$id)
                    return $this->redirectToRoute('orgaComponentFormWithoutId',["uniqueComponentName" => $uniqueComponentName]);
                else
                    return $this->redirectToRoute('orgaComponentForm',['uniqueComponentName' => $uniqueComponentName,'id' => $id]);
            }
        }
        // Fill form
        return $this->render(
            'semappsBundle:'.ucfirst($uniqueComponentName).':'.$uniqueComponentName.'Form.html.twig',[
                'organization' => $organization,
                'importForm'=> ($importForm != null)? $importForm->createView() : null,
                "form" => $form->createView(),
                "entityUri" => $sfLink
            ]
        );
    }

    public function getElement($id =null)
    {
        if($id == null)
            return $this->getOrgaByGraph($this->getGraph($id));
        else
            return $this->getOrga($id);
    }

    public function getSfLink($id = null)
    {
        $sfLink = $this->getElement($id)->getSfOrganisation();
        return ($sfLink)? $sfLink:null;
    }
    public function getGraph($id = null)
    {
        if($id == null){
            /** @var contextManager $contextManager */
            $contextManager = $this->container->get("semappsBundle.contextManager");
            return $contextManager->getContext($this->getUser()->getSfLink())['context'];
        }
        else
            return $this->getElement($id)->getGraphURI();
    }

}
