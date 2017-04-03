<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\Form\OrganisationMemberType;
use GrandsVoisinsBundle\Form\OrganizationType;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use SimpleExcel\SimpleExcel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
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
          OrganisationMemberType::class,
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
          'GrandsVoisinsBundle:Organization:home.html.twig',
          array(
            "tabOrga"             => GrandsVoisinsConfig::$buildings,
            "organisations"       => $organisations,
            "formAddOrganisation" => $form->createView(),
          )
        );
    }

    public function orgaExportAction()
    {
        $lines              = [];
        $sfClient           = $this->container->get('semantic_forms.client');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );
        $organisations      = $organisationEntity->findAll();
        $columns            = [];

        foreach ($organisations as $organisation) {
            // Sparql request.
            $properties = $sfClient->uriProperties(
              $organisation->getGraphURI()
            );
            // We have key / pair values.
            $lines[] = $properties;
            // Save new columns if some are missing.
            $columns = array_unique(
              array_merge($columns, array_keys($properties))
            );
        }

        $output = [];
        // Rebuild array based on strict columns list.
        foreach ($lines as $incompleteLine) {
            $line = [];
            foreach ($columns as $key) {
                $line[$key] = isset($incompleteLine[$key]) ? $incompleteLine[$key] : '';
            }
            $output[] = $line;
        }

        // Append first lint.
        array_unshift($output, $columns);

        $excel = new SimpleExcel('csv');
        /** @var \SimpleExcel\Writer\CSVWriter $writer */
        $writer = $excel->writer;
        // Fill.
        $writer->setData(
          $output
        );
        $writer->setDelimiter(";");
        $writer->saveFile('LesGrandsVoisins-'.date('Y_m_d'));

        return $this->redirectToRoute('all_orga');
    }

    public function organisationAction(Request $request, $orgaId = null)
    {
        /** @var $user \GrandsVoisinsBundle\Entity\User */
        $user     = $this->getUser();
        $sfClient = $this->container->get('semantic_forms.client');

        /* @var $organisationEntity \GrandsVoisinsBundle\Repository\OrganisationRepository */
        // Ask database to know if organization has been already created.
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );
        $orgaId             = ($orgaId != null && $this->getUser()->getRoles(
            'SUPER_ADMIN'
          )) ? $orgaId : $this->GetUser()->getFkOrganisation();
        /* @var $organization \GrandsVoisinsBundle\Entity\Organisation */
        $organization = $organisationEntity->findOneById(
          $orgaId
        );

        $oldPictureName = $organization->getOrganisationPicture();

        $sfLink = $organization->getSfOrganisation();

        // Build main form.
        $options = [
          'login'                 => $user->getEmail(),
          'password'              => $user->getSfUser(),
          'graphURI'              => $organization->getGraphURI(),
          'client'                => $sfClient,
          'spec'                  => SemanticFormsClient::SPEC_ORGANIZATION,
          'lookupUrlLabel'        => $this->generateUrl(
            'webserviceFieldUriLabel'
          ),
          'lookupUrlPerson'       => $this->generateUrl(
            'webserviceFieldUriSearch'
          ),
          'lookupUrlOrganization' => $this->generateUrl(
            'webserviceFieldUriSearch'
          ),
          'values'                => $sfLink,
        ];

        /** @var \VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType $form */
        $form = $this->createForm(
          OrganizationType::class,
          $organization,
          // Options.
          $options
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Manage picture.
            $newPicture = $organization->getOrganisationPicture();
            if ($newPicture) {
                // Remove old picture.
                $fileUploader = $this->get('GrandsVoisinsBundle.fileUploader');
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
            } else {
                $organization->setOrganisationPicture($oldPictureName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($organization);
            $em->flush();

            $this->addFlash(
              'success',
              'Les données de l\'organisation ont bien été mises à jour.'
            );

            return $this->redirectToRoute('detail_orga');
        }
        dump(!$sfLink);
        if (!$sfLink) {
            // Get the main Organization entity.
            $organizationRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('GrandsVoisinsBundle:Organisation');

            // Update sfOrganisation.
            $organizationRepository
                ->createQueryBuilder('q')
                ->update()
                ->set('q.sfOrganisation', ':link')
                ->where('q.id=:id')
                ->setParameter('link', $form->uri)
                ->setParameter('id', $organization->getId())
                ->getQuery()
                ->execute();
        }
        // Fill form
        return $this->render(
          'GrandsVoisinsBundle:Organization:organization.html.twig',
          array(
            'form'         => $form->createView(),
            'organization' => $organization,
            'entityUri'    => $sfLink,
          )
        );
    }

    public function saveOrganisationAction()
    {
        $edit = $_POST["edit"];
        $id   = $_POST["id"];
        unset($_POST["edit"]);
        unset($_POST["id"]);

        $sfClient = $this
          ->container
          ->get('semantic_forms.client');
        $sfClient
          ->verifMember($_POST, $_POST["graphURI"], $_POST["uri"]);
        $info = $sfClient
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

            return $this->redirectToRoute(
              'detail_orga_edit',
              ['orgaId' => $id]
            );

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
