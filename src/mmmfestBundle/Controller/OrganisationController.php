<?php

namespace mmmfestBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use mmmfestBundle\Entity\Organisation;
use mmmfestBundle\Entity\User;
use mmmfestBundle\Form\OrganisationMemberType;
use mmmfestBundle\Form\OrganizationType;
use mmmfestBundle\mmmfestConfig;
use SimpleExcel\SimpleExcel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrganisationController extends Controller
{

    public function allAction(Request $request)
    {
        /** @var \mmmfestBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('mmmfestBundle.encryption');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'mmmfestBundle:Organisation'
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
              mmmfestConfig::PREFIX.$organisation->getId().'-org'
            );
            $em->flush();

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

            $user->setSfUser($encryption->encrypt($randomPassword));

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
                  $em->getRepository('mmmfestBundle:Organisation')->find(
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
                  $em->getRepository('mmmfestBundle:User')->find(
                    $user->getId()
                  )
                );
                $em->remove(
                  $em->getRepository('mmmfestBundle:Organisation')->find(
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
            $mailer = $this->get('mmmfestBundle.EventListener.SendMail');
            $result = $mailer->sendConfirmMessage(
                $mailer::TYPE_RESPONSIBLE,
                $user,
                $organisation,
                $url
              );

            // TODO Grant permission to edit same organisation as current user.
            // Display message.
            if($result){
            $this->addFlash(
              'success',
              'Un compte à bien été créé pour <b>'.
              $user->getUsername().
              '</b>. Un email a été envoyé à <b>'.
              $user->getEmail().
              '</b> pour lui communiquer ses informations de connexion.'
            );
            }else{
                $this->addFlash(
                  'danger',
                  'Un compte à bien été créé pour <b>'.
                  $user->getUsername().
                  "</b>. mais l'email n'est pas parti à l'adresse <b>".
                  $user->getEmail().
                  '</b>'
                );
            }
            return $this->redirectToRoute('all_orga');
        }

        return $this->render(
          'mmmfestBundle:Organization:home.html.twig',
          array(
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
          'mmmfestBundle:Organisation'
        );
        $organisations      = $organisationEntity->findAll();
        $columns            = [];

        foreach ($organisations as $organisation) {
            // Sparql request.
            $properties = $sfClient->uriProperties(
              $organisation->getSfOrganisation()
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
                $line[$key] = isset($incompleteLine[$key]) ? is_array($incompleteLine[$key])? implode(',',$incompleteLine[$key]) : $incompleteLine[$key] : '';
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
        $writer->saveFile('mmmfest-'.date('Y_m_d'));

        return $this->redirectToRoute('all_orga');
    }

    public function organisationAction(Request $request, $orgaId = null)
    {
        /** @var $user \mmmfestBundle\Entity\User */
        $user     = $this->getUser();
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \mmmfestBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('mmmfestBundle.encryption');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
				$organizationConf = $this->getParameter('organizationConf');
        /* @var $organisationEntity \mmmfestBundle\Repository\OrganisationRepository */
        // Ask database to know if organization has been already created.
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'mmmfestBundle:Organisation'
        );
        if($orgaId != null && $user->hasRole(
            'ROLE_SUPER_ADMIN'
        ) && $user->getFkOrganisation() != $orgaId){
            $organization = $organisationEntity->find(
                $orgaId
            );
            $userRepository = $this->getDoctrine()->getManager()->getRepository(
                'mmmfestBundle:User'
            );

            $responsable = $userRepository->find($organization->getFkResponsable());
            $sfUser = $responsable->getEmail();
            $sfPassword = $encryption->decrypt($responsable->getSfUser());
        }
        else{
            $organization = $organisationEntity->findOneById(
                $user->getFkOrganisation()
            );
            $sfUser = $user->getEmail();
            $sfPassword = $encryption->decrypt($user->getSfUser());
        }


        /* @var $organization \mmmfestBundle\Entity\Organisation */


        $oldPictureName = $organization->getOrganisationPicture();

        $sfLink = $organization->getSfOrganisation();

        // Build main form.
        $options = [
          'login'                 => $sfUser,
          'password'              => $sfPassword,
          'graphURI'              => $organization->getGraphURI(),
          'client'                => $sfClient,
          'spec'                  => $organizationConf['spec'],
          'sfConf'               => $organizationConf,
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
          'role'                  => $user->getRoles(),
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
            $newPicture = $form->get('organisationPicture')->getData();
            if ($newPicture) {
                // Remove old picture.
                $fileUploader = $this->get('mmmfestBundle.fileUploader');
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

                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
                $sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addDelete(
                      $sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      '?o',
                      $sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL))
                    ->addWhere(
                      $sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      '?o',
                      $sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sfClient->update($sparql->getQuery());

                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addInsert(
                      $sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      $sparql->formatValue($fileUploader->generateUrlForFile($organization->getOrganisationPicture()),$sparql::VALUE_TYPE_TEXT),
                      $sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sfClient->update($sparql->getQuery());

            } else {
                $organization->setOrganisationPicture($oldPictureName);
            }

            $em = $this->getDoctrine()->getManager();


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
            if(!$orgaId)
                return $this->redirectToRoute('detail_orga');
            else
                return $this->redirectToRoute('detail_orga_edit',['orgaId' => $orgaId]);
        }
        // Fill form
        return $this->render(
          'mmmfestBundle:Organization:organization.html.twig',
          array(
            'form'         => $form->createView(),
            'organization' => $organization,
            'entityUri'    => $sfLink,
          )
        );
    }

    public function orgaDeleteAction($orgaId)
    {
        $organisationRepository = $this->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:Organisation');

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
