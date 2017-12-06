<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Entity\Organisation;
use semappsBundle\Entity\User;
use semappsBundle\Form\OrganisationMemberType;
use semappsBundle\semappsConfig;
use SimpleExcel\SimpleExcel;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrganisationController extends UniqueComponentController
{

    public function allAction(Request $request)
    {
        /** @var \semappsBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('semappsBundle.encryption');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'semappsBundle:Organisation'
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
              semappsConfig::PREFIX.$organisation->getId().'-org'
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
                  $em->getRepository('semappsBundle:Organisation')->find(
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
                  $em->getRepository('semappsBundle:User')->find(
                    $user->getId()
                  )
                );
                $em->remove(
                  $em->getRepository('semappsBundle:Organisation')->find(
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
            $mailer = $this->get('semappsBundle.EventListener.SendMail');
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
          'semappsBundle:Organisation:home.html.twig',
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
          'semappsBundle:Organisation'
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
        $writer->saveFile('SemApps-'.date('Y_m_d'));

        return $this->redirectToRoute('all_orga');
    }

    public function orgaDeleteAction($orgaId)
    {
        $organisationRepository = $this->getDoctrine()
          ->getManager()
          ->getRepository('semappsBundle:Organisation');

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

		public function addAction($uniqueComponentName,$id =null,Request $request)
		{
				$sfClient       = $this->container->get('semantic_forms.client');
				$organization = $this->getOrga($id);
				/** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
				$sparqlClient   = $this->container->get('sparqlbundle.client');
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

								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
								$sparql->addPrefixes($sparql->prefixes)
									->addPrefix('pair','http://virtual-assembly.org/pair#')
									->addDelete(
										$sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
										'pair:image',
										'?o',
										$sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL))
									->addWhere(
										$sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
										'pair:image',
										'?o',
										$sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL));
								$sfClient->update($sparql->getQuery());

								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
								$sparql->addPrefixes($sparql->prefixes)
									->addPrefix('pair','http://virtual-assembly.org/pair#')
									->addInsert(
										$sparql->formatValue($sfLink, $sparql::VALUE_TYPE_URL),
										'pair:image',
										$sparql->formatValue($fileUploader->generateUrlForFile($organization->getOrganisationPicture()),$sparql::VALUE_TYPE_TEXT),
										$sparql->formatValue($organization->getGraphURI(),$sparql::VALUE_TYPE_URL));
								$sfClient->update($sparql->getQuery());

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
								//importer le profile
								$sfClient->import($uri);
								//déplacer dans le graph de l'orga
								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT);
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
				return $this->getOrga($id);
		}

		public function getSfLink($id = null)
		{

				return $this->getElement($id)->getSfOrganisation();
		}


}
