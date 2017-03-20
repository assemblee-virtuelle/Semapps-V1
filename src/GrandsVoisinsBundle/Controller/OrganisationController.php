<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\Form\OrganisationType;
use GrandsVoisinsBundle\GrandsVoisinsBundle;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrganisationController extends Controller
{

    public function allAction(Request $request)
    {

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );
        $organisations      = $organisationEntity->findAll();

        //form pour l'organisation
        $organisation = new Organisation();
        $form         = $this->get('form.factory')->create(
          OrganisationType::class,
          $organisation
        );


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            //for the organisation
            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);
            try {
                $em->flush($organisation);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'danger',
                    "le nom de l'orgnanisation que vous avez saisi est déjà présent"
                );

                return $this->redirectToRoute('all_orga');
            }
            $organisation->setGraphURI(
              GrandsVoisinsConfig::PREFIX.$organisation->getId().'-org'
            );
            $em->flush();

            //TODO find a way to call teamAction in admin
            //for the user
            $user = new User();

            $user->setUsername($form->get('username')->getData());
            $user->setEmail($form->get('email')->getData());

            // Generate password.
            $tokenGenerator = $this->container->get(
              'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $user->setPassword(
              password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $user->setSfUser($randomPassword);

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $user->setConfirmationToken($conf_token);

            //Set the roles
            $user->addRole("ROLE_ADMIN");

            $user->setFkOrganisation($organisation->getId());

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                //removing the organization added before
                $em = $this->getDoctrine()->resetManager();
                $em->remove(
                  $em->getRepository('GrandsVoisinsBundle:Organisation')->find(
                    $organisation->getId()
                  )
                );
                $em->flush();
                $this->addFlash(
                  'danger',
                  "l'utilisateur saisi est déjà présent"
                );

                return $this->redirectToRoute('all_orga');
            }

            $organisation->setFkResponsable($user->getId());
            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                //removing the organization and the user added before
                $em = $this->getDoctrine()->resetManager();
                $em->remove(
                  $em->getRepository('GrandsVoisinsBundle:User')->find(
                    $user->getId()
                  )
                );
                $em->remove(
                  $em->getRepository('GrandsVoisinsBundle:Organisation')->find(
                    $organisation->getId()
                  )
                );
                $em->flush();
                $this->addFlash(
                  'danger',
                  "Problème lors de la mise à jour des champs, veuillez contacter un administrateur"
                );

                return $this->redirectToRoute('all_orga');
            }
            $url = $this->generateUrl(
              'fos_user_registration_confirm',
              array('token' => $conf_token),
              UrlGeneratorInterface::ABSOLUTE_URL
            );
            // send email to the new organization
            $this->get('GrandsVoisinsBundle.EventListener.SendMail')
              ->sendConfirmMessage(
                $user,
                GrandsVoisinsConfig::ORGANISATION,
                $url,
                $randomPassword,
                $organisation
              );

            // TODO Grant permission to edit same organisation as current user.
            // Display message.
            $this->addFlash(
              'success',
              'Un compte à bien été créé pour <b>'.
              $user->getUsername().
              '</b>. Un email a été envoyé à <b>'.
              $user->getEmail().
              '</b> pour lui communiquer ses informations de connexion.'
            );

            return $this->redirectToRoute('all_orga');
        }

        return $this->render(
          'GrandsVoisinsBundle:Organisation:home.html.twig',
          array(
            "tabOrga"             => GrandsVoisinsConfig::$buildings,
            "organisations"       => $organisations,
            "formAddOrganisation" => $form->createView(),
          )
        );
    }


    public function newOrganisationAction(Request $request)
    {
        $sfClient = $this->container->get('semantic_forms.client');

        /* @var $organisation \GrandsVoisinsBundle\Repository\OrganisationRepository */
        // questionner la base pour savoir si l'orga est deja créer
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation = $organisationEntity->findOneById(
          $this->GetUser()->getFkOrganisation()
        );

        if (is_null($organisation->getSfOrganisation())) {
            $json = $sfClient->create(SemanticFormsClient::ORGANISATION);
            $edit = false;
        } else {
            $json = $sfClient->edit(
              $organisation->getSfOrganisation(),
              SemanticFormsClient::ORGANISATION
            );
            $edit = true;
        }
        if (!$json) {
            $this->addFlash(
              'danger',
              'Une erreur s\'est produite lors de l\'affichage du formulaire'
            );

            return $this->redirectToRoute('home');
        }


        // Picture for organization
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation_picture = $organisationEntity->findOneById(
          $this->GetUser()->getFkOrganisation()
        );

        $picture = $this->createFormBuilder($organisation_picture)
          ->add(
            'OrganisationPicture',
            FileType::class,
            array('data_class' => null)
          )
          ->add(
            'oldPicture',
            HiddenType::class,
            array(
              'mapped' => false,
              'data'   => $organisation_picture->getOrganisationPicture(),
            )
          )
          ->getForm();

        $picture->handleRequest($request);

        if ($picture->isSubmitted() && $picture->isValid()) {
            if ($picture->get('oldPicture')->getData()) {
                $oldDir      = $this->get('GrandsVoisinsBundle.fileUploader')
                  ->getTargetDir(
                    $picture->get('oldPicture')->getData()
                  );
                $oldFileName = $picture->get('oldPicture')->getData();
                // Check if file exists to avoid all errors.
                if (is_file($oldDir.'/'.$oldFileName)) {
                    $this->get('GrandsVoisinsBundle.fileUploader')
                      ->remove($oldFileName);
                }
            }
            $organisation_picture->setOrganisationPicture(
              $this->get('GrandsVoisinsBundle.fileUploader')->upload(
                $organisation_picture->getOrganisationPicture()
              )
            );
            $em = $this->getDoctrine()->getManager();
            $em->persist($organisation_picture);
            $em->flush();

            return $this->redirectToRoute('detail_orga');
        }

        return $this->render(
          'GrandsVoisinsBundle:Organisation:organisation.html.twig',
          array(
            'organisation'        => $json,
            'edit'                => $edit,
            'graphURI'            => $organisation->getGraphURI(),
            'picture'             => $picture->createView(),
            'OrganisationPicture' => $organisation->getOrganisationPicture(),
          )
        );
    }

    public function saveOrganisationAction()
    {
        $edit = $_POST["edit"];
        unset($_POST["edit"]);

        $info = $this->container
          ->get('semantic_forms.client')
          ->send(
            $_POST,
            $this->getUser()->getEmail(),
            $this->getUser()->getSfUser()
          );


        //TODO: a modifier pour prendre l'utilisateur courant !
        if ($info == 200) {
            if (!$edit) {
                $organisationEntity = $this->getDoctrine()
                  ->getManager()
                  ->getRepository('GrandsVoisinsBundle:Organisation');
                $query              = $organisationEntity->createQueryBuilder(
                  'q'
                )
                  ->update()
                  ->set('q.sfOrganisation', ':link')
                  ->where('q.id=:id')
                  ->setParameter('link', $_POST["uri"])
                  ->setParameter('id', $this->getUser()->getfkOrganisation())
                  ->getQuery();
                $query->getResult();
            }

            $this->addFlash(
              'success',
              'Les modifications ont bien été prises en compte.'
            );

            return $this->redirectToRoute('detail_orga');

        } else {
            $this->addFlash(
              'success',
              'Une erreur s\'est produite lors de la sauvegarde du formulaire'
            );

            return $this->redirectToRoute('organisation');
        }
    }

    public function orgaDeleteAction($orgaId)
    {
        $organisationRepository = $this->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:Organisation');

        $organisation  = $organisationRepository->find($orgaId);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$organisation) {
            // Display error message.
            $this->addFlash(
              'danger',
              'Organisation introuvable.'
            );
        } else {
            // Delete.
            $entityManager->remove($organisation);

            $entityManager
              ->getConnection()
              ->prepare(
                'DELETE FROM user WHERE fk_organisation = :id_organisation'
              )
              ->execute([':id_organisation' => $organisation->getId()]);

            $entityManager->flush();
            // Display success message.
            $this->addFlash(
              'success',
              'L\'organisation <b>'.
              $organisation->getName().
              '</b> a bien été supprimée.'
            );
        }

        return $this->redirectToRoute('all_orga');
    }


}
