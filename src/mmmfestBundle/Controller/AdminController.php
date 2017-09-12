<?php

namespace mmmfestBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use mmmfestBundle\Form\ProfileType;
use mmmfestBundle\Form\RegisterType;
use mmmfestBundle\Form\UserType;
use mmmfestBundle\mmmfestConfig;
use mmmfestBundle\Form\AdminSettings;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{

    public function homeAction()
    {
        return $this->redirectToRoute('fos_user_profile_show');
    }

    public function registerAction(Request $request)
    {
    		$title = [
    			'lundi 4 octobre',
    			'mardi 5 octobre',
    			'mercredi 6 octobre',
    			'jeudi 7 octobre',
    			'vendredi 8 octobre',
    			'samedi 9 octobre',
    			'dimanche 10 octobre',
				];
        /** @var \mmmfestBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('mmmfestBundle.encryption');
        $userRepository = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:User');
        //get all organization
        $organisationRepository =  $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:Organisation');
        $organisations = $organisationRepository->findBy([],['name' =>'ASC']);

        $tabOrga= [];
        //build a tab with id and name of each organization for the choice type
        foreach ($organisations as $organisation){
            $tabOrga[$organisation->getId()] = $organisation->getName();
        }
        //get the form
        $form = $this->createForm(
          RegisterType::class,
          null,
          // Options.
          []
        );
        //add the ChoiceType field with all orga
        $form->add(
          'organisation',
          ChoiceType::class,array(
            'mapped'  => false,
            'choices' => array_flip($tabOrga)
            )
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tokenGenerator = $this->container->get(
              'fos_user.util.token_generator'
            );
            $data->setPassword(
              password_hash($form->get('password')->getData(), PASSWORD_BCRYPT, ['cost' => 13])
            );

            $data->setSfUser($encryption->encrypt($form->get('password')->getData()));

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $data->setConfirmationToken($conf_token);

            //Set the roles
            $data->addRole('ROLE_MEMBER');

            $data->setFkOrganisation($form->get('organisation')->getData());

            // Save the different diner
						$week =[];
						for ($i = 1; $i <= 7; $i++ ){
								$week[$i][0] = $form->get("matin".$i)->getData();
								$week[$i][1] = $form->get("midi".$i)->getData();
								$week[$i][2] = $form->get("soir".$i)->getData();
						}
						$data->setRepas(json_encode($week));
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "l'utilisateur saisi existe déjà, vous pouvez essayer de réinitialiser votre mot de passe en renseignant votre e-mail ou votre login");

                return $this->redirectToRoute('fos_user_resetting_request',array('email' => $data->getEmail()));
            }
            $this->addFlash('success','Merci à toi cher Voisin, nous avons bien pris en compte ton inscription,
             nous allons la valider dans les prochaines heures, après quoi tu recevras un mail de confirmation :-) A très bientôt sur la carto ! ');

            //notification
            $usersSuperAdmin = $userRepository->createQueryBuilder('q')->select('q.email')->where("q.roles LIKE '%ROLE_SUPER_ADMIN%'")->getQuery()->getResult();
            $responsible = $userRepository->findOneBy(['fkOrganisation' => $form->get('organisation')->getData()]);
            $listOfEmail= [];
            $organisation = $organisationRepository->find($form->get('organisation')->getData());
            array_push($listOfEmail,$responsible->getEmail());
            foreach ($usersSuperAdmin as $superuser){
                array_push($listOfEmail,$superuser["email"]);
            }
            $mailer = $this->get('mmmfestBundle.EventListener.SendMail');
            $mailer->sendNotification($mailer::TYPE_NOTIFICATION,$data,$organisation,array_unique($listOfEmail));

            return $this->redirectToRoute('fos_user_security_login');
        }
        // Fill form
        return $this->render(
          'mmmfestBundle:Admin:register.html.twig',
          array(
            'form'      => $form->createView(),
						'title' 	=> $title
          )
        );
    }

    public function listUserAction()
    {

        $users = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:User')
          ->findAll();

        //$userEnabled = $userRepository->findBy(array('enabled' => 1));
        //$userDisabled = $userRepository->findBy(array('enabled' => 0));

        $tabUserEnabled = $tabUserDisabled = [];
        foreach ($users as $user){
            $organization = $this
              ->getDoctrine()
              ->getManager()
              ->getRepository('mmmfestBundle:Organisation')
              ->findOneBy(array('id' => $user->getFkOrganisation()));

            if($user->isEnabled()){

                $tabUserEnabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserEnabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserEnabled[$user->getId()]["lastLogin"] = $user->getLastLogin();
                $tabUserEnabled[$user->getId()]["organization"] = $organization->getName();
            }
            else{
                $tabUserDisabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserDisabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserDisabled[$user->getId()]["organization"] = $organization->getName();
                $tabUserDisabled[$user->getId()]["isResponsible"] = $organization->getFkResponsable() == $user->getId();
            }

        }

        return $this->render(
          'mmmfestBundle:Admin:listUser.html.twig',
          array(
            'userEnabled'      => $tabUserEnabled,
            'userDisabled'     => $tabUserDisabled,
            'nameRoute'        => 'user',
            'usersRolesLabels' => [
              'ROLE_SUPER_ADMIN' => 'Super admin',
              'ROLE_ADMIN'       => 'Administration',
              'ROLE_MEMBER'      => 'Member',
            ],
          )
        );


    }

    public function sendUserAction($userId,$nameRoute = 'team'){
        $user = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:User')
          ->find($userId);
        $organisation = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:Organisation')
          ->find($user->getFkOrganisation());
        $url = $this->generateUrl(
          'fos_user_registration_confirm',
          array('token' => $user->getConfirmationToken()),
          UrlGeneratorInterface::ABSOLUTE_URL
        );
        //send email to the new user
        $mailer = $this->get('mmmfestBundle.EventListener.SendMail');
        $result = $mailer->sendConfirmMessage(
          ($user->getId() == $organisation->getFkResponsable()) ? $mailer::TYPE_RESPONSIBLE : $mailer::TYPE_USER,
            $user,
            $organisation,
            $url
            //$user->getSfUser()
          );
        if($result){
            $this->addFlash('info',"email envoyé pour l'utilisateur <b>".$user->getUsername()."</b> à l'adresse <b>".$user->getEmail()."</b>");
        }
        return $this->redirectToRoute($nameRoute);
    }

    public function profileAction(Request $request)
    {
        /** @var $user \mmmfestBundle\Entity\User */
        $user           = $this->getUser();
        $userSfLink     = $user->getSfLink();
        /** @var \mmmfestBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('mmmfestBundle.encryption');
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient       = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
        $oldPictureName = $user->getPictureName();
        //$predicatImage  = $this->getParameter('semantic_forms.fields_aliases')['image'];
        $organisation = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('mmmfestBundle:Organisation')
            ->find($this->getUser()->getFkOrganisation());

        // Build main form.
        $options = [
            'login'                 => $user->getEmail(),
            'password'              => $encryption->decrypt($user->getSfUser()),
            'graphURI'              => $organisation->getGraphURI(),
            'client'                => $sfClient,
            'reverse'               => mmmfestConfig::REVERSE,
            'spec'                  => mmmfestConfig::SPEC_PERSON,
            'lookupUrlLabel'        => $this->generateUrl(
                'webserviceFieldUriLabel'
            ),
            'lookupUrlPerson'       => $this->generateUrl(
                'webserviceFieldUriSearch'
            ),
            'lookupUrlOrganization' => $this->generateUrl(
                'webserviceFieldUriSearch'
            ),
            'values'                => $userSfLink,
        ];

        /** @var \VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType $form */
        $form = $this->createForm(
            ProfileType::class,
            $user,
            // Options.
            $options
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Manage picture.
            $newPicture = $form->get('pictureName')->getData();
            if ($newPicture) {
                // Remove old picture.
                $fileUploader = $this->get('mmmfestBundle.fileUploader');
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
                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
                $sparql->addPrefixes($sparql->prefixes)
										->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addDelete(
                      $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      '?o',
                      $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL))
                    ->addWhere(
                      $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      '?o',
                      $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sfClient->update($sparql->getQuery());
								//dump($sparql->getQuery());
                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $sparql->addPrefixes($sparql->prefixes)
										->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addInsert(
                      $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                      'default:image',
                      $sparql->formatValue($fileUploader->generateUrlForFile($user->getPictureName()),$sparql::VALUE_TYPE_TEXT),
                      $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
                    $sfClient->update($sparql->getQuery());
								//dump($sparql->getQuery());
								//exit;
            } else {
                $user->setPictureName($oldPictureName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // User never had a sf link, so save it.
            if (!$userSfLink) {
                // Get the main user entity.
                $userRepository = $this
                    ->getDoctrine()
                    ->getManager()
                    ->getRepository('mmmfestBundle:User');

                // Update sfLink.
                $userRepository
                    ->createQueryBuilder('q')
                    ->update()
                    ->set('q.sfLink', ':link')
                    ->where('q.id=:id')
                    ->setParameter('link', $form->uri)
                    ->setParameter('id', $this->getUser()->getId())
                    ->getQuery()
                    ->execute();
                //hasMember
                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $uriOrgaFormatted = $sparql->formatValue($organisation->getSfOrganisation(),$sparql::VALUE_TYPE_URL);
                $uripersonFormatted = $sparql->formatValue($form->uri,$sparql::VALUE_TYPE_URL);
                $graphFormatted = $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL);
                $sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addInsert($uriOrgaFormatted,'default:hasMember',$uripersonFormatted,$graphFormatted);
                //dump($sparql->getQuery());
                $sfClient->update($sparql->getQuery());
                //memberOf
                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                    ->addInsert($uripersonFormatted,'default:memberOf',$uriOrgaFormatted,$graphFormatted);
                //dump($sparql->getQuery());
                $sfClient->update($sparql->getQuery());

            }

            $this->addFlash(
                'success',
                'Votre profil a bien été mis à jour.'
            );

            return $this->redirectToRoute('fos_user_profile_show');
        }
				// import
				$importForm = null;
				if(!$userSfLink){
						$importForm = $this->createFormBuilder();
						$importForm->add('import',UrlType::class);
						$importForm->add('save',SubmitType::class);
						$importForm = $importForm->getForm();
						$importForm->handleRequest($request);

						if ($importForm->isSubmitted() && $importForm->isValid()) {
								$uri = $importForm->get('import')->getData();
								$userRepository = $this
									->getDoctrine()
									->getManager()
									->getRepository('GrandsVoisinsBundle:User');
								// Update sfLink.
								$userRepository
									->createQueryBuilder('q')
									->update()
									->set('q.sfLink', ':link')
									->where('q.id=:id')
									->setParameter('link', $uri)
									->setParameter('id', $this->getUser()->getId())
									->getQuery()
									->execute();
								//importer le profile
								$sfClient->import($uri);
								//déplacer dans le graph de l'orga
								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT);
								$uriOrgaFormatted = $sparql->formatValue($organisation->getSfOrganisation(),$sparql::VALUE_TYPE_URL);
								$uripersonFormatted = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);
								$graphFormatted = $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL);

								$sparql->addPrefixes($sparql->prefixes)
												->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#');
								//$sparql->addDelete("?s","?p","?o",$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL));
								$sparql->addWhere("?s","?p","?o",$sparql->formatValue($uri,$sparql::VALUE_TYPE_URL));
								$sparql->addInsert("?s","?p","?o",$graphFormatted);
								//dump($sparql->getQuery());
								$sfClient->update($sparql->getQuery());
								//hasMember
								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);

								$sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
									->addInsert($uriOrgaFormatted,'org:hasMember',$uripersonFormatted,$graphFormatted);
								//dump($sparql->getQuery());
								$sfClient->update($sparql->getQuery());
								//memberOf
								$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
								$sparql->addPrefixes($sparql->prefixes)
									->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
									->addInsert($uripersonFormatted,'org:memberOf',$uriOrgaFormatted,$graphFormatted);
								//dump($sparql->getQuery());
								$sfClient->update($sparql->getQuery());
								return $this->redirectToRoute('fos_user_profile_show');
						}
				}

        // Fill form
        return $this->render(
            'mmmfestBundle:Admin:profile.html.twig',
            array(
                'form'      => $form->createView(),
                'entityUri' => $userSfLink,
								'importForm'=> ($importForm != null)? $importForm->createView() : null
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function teamAction(Request $request)
    {
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient       = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
        // Find all users.
        $userManager         = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'mmmfestBundle:User'
            );
        $organisationManager = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'mmmfestBundle:Organisation'
            );
        $users               = $userManager->findBy(
            array('fkOrganisation' => $this->getUser()->getFkOrganisation())
        );
        $organisation       = $organisationManager->find(
            $this->getUser()->getFkOrganisation()
        );
        $form                = $this->get('form.factory')->create(
            UserType::class
        );
        $idResponsible = $organisation->getFkResponsable();

        // using the field username_canonical to have the name and forname of each user
        foreach ($users as $user){
            //TODO make function to find something about someone
            $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);
            $sparql->addPrefixes($sparql->prefixes)
							->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
                ->addSelect('?name')
                ->addSelect('?forname')
                ->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
                  'default:lastName',
                  '?name',
                  $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL))
                ->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
                  'default:firstName',
                  '?forname',
                  $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
            $result = $sfClient->sparql($sparql->getQuery());
            $nom = $prenom = "";
            if (array_key_exists(0,$result["results"]["bindings"])){
                //dump($result["results"]["bindings"]);
                $nom = (isset($result["results"]["bindings"][0]['name']["value"]))? $result["results"]["bindings"][0]['name']["value"] : "";
                $prenom = (isset($result["results"]["bindings"][0]['forname']["value"]))? $result["results"]["bindings"][0]['forname']["value"] : "";
            }
            $user->setUsernameCanonical($nom . ' ' . $prenom);

        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get posted data of type user
            $data = $form->getData();
            /** @var \mmmfestBundle\Services\Encryption $encryption */
            $encryption = $this->container->get('mmmfestBundle.encryption');
            // Generate password.
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $data->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $data->setSfUser($encryption->encrypt($randomPassword));

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $data->setConfirmationToken($conf_token);

            //Set the roles
            $data->addRole($form->get('access')->getData());

            $data->setFkOrganisation($this->getUser()->getFkOrganisation());
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "l'utilisateur saisi existe déjà");

                return $this->redirectToRoute('team');
            }
            $url = $this->generateUrl(
                'fos_user_registration_confirm',
                array('token' => $conf_token),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //send email to the new user
            $mailer =  $this->get('mmmfestBundle.EventListener.SendMail');
            $result =$mailer->sendConfirmMessage(
                  $mailer::TYPE_USER,
                    $data,
                    $organisation,
                    $url
                    //$randomPassword
                );

            // TODO Grant permission to edit same organisation as current user.
            // Display message.
            if($result){
                $this->addFlash(
                  'success',
                  'Un compte à bien été créé pour <b>'.
                  $data->getUsername().
                  '</b>. Un email a été envoyé à <b>'.
                  $data->getEmail().
                  '</b> pour lui communiquer ses informations de connexion.'
                );
            }else{
                $this->addFlash(
                  'danger',
                  'Un compte à bien été créé pour <b>'.
                  $data->getUsername().
                  "</b>. mais l'email n'est pas parti à l'adresse <b>".
                  $data->getEmail().
                  '</b>'
                );
            }

            // Go back to team page.
            return $this->redirectToRoute('team');
        }

        return $this->render(
            'mmmfestBundle:Admin:team.html.twig',
            array(
                'users'            => $users,
                'idResponsable'    => $idResponsible,
                'usersRolesLabels' => [
                    'ROLE_SUPER_ADMIN' => 'Super admin',
                    'ROLE_ADMIN'       => 'Administration',
                    'ROLE_MEMBER'      => 'Member',
                ],
                'formAddUser'      => $form->createView(),
                'nameRoute'        => 'team'
            )
        );
    }

    public function userDeleteAction($userId)
    {
        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $this->get('fos_user.user_manager');
        $user        = $userManager->findUserBy(['id' => $userId]);

        if (!$user) {
            // Display error message.
            $this->addFlash(
                'danger',
                'Utilisateur introuvable.'
            );
        } else {
            // Delete.
            $userManager->deleteUser($user);
            // Display success message.
            $this->addFlash(
                'success',
                'Le compte de <b>'.
                $user->getUsername().
                '</b> a bien été supprimé.'
            );
        }

        return $this->redirectToRoute('team');
    }

    public function settingsAction(Request $request)
    {
        $user = $this->GetUser();
        $form = $this->get('form.factory')->create(AdminSettings::class, $user);
        $em   = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        $isOldPasswordMatch = (password_verify(
            $form->get('password')->getData(),
            $this->getUser()->getPassword()
        ));
        $isNewPasswordMatch = ($form->get('passwordNew')->getdata(
            ) == $form->get('passwordNewConfirm')->getdata());
        $isChangedUsername  = ($form->get('username')->getdata(
            ) != $this->getUser()->getUsername());
        $isOK               = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isOldPasswordMatch) {
                if ($isChangedUsername) {
                    $user->setUsername($form->get('username')->getdata());
                    $isOK = true;
                }
                if ($form->get('passwordNew')->getdata() && $form->get(
                        'passwordNewConfirm'
                    )->getdata()
                ) {
                    if ($isNewPasswordMatch) {
                        $user->setPassword(
                            password_hash(
                                $form->get('passwordNew')->getdata(),
                                PASSWORD_BCRYPT,
                                ['cost' => 13]
                            )
                        );
                        $isOK = true;
                    } else {
                        $this->addFlash(
                            'info',
                            "les mots de passe saisi ne correspondent pas"
                        );
                    }
                }
                $em->persist($user);
                try {
                    if ($isOK) {
                        $em->flush();
                        $this->addFlash(
                            "success",
                            "les informations ont été correctement enregistés"
                        );
                    }

                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        "le nom d'utilisateur saisi existe déjà"
                    );

                    return $this->redirectToRoute('settings');
                }
            } else {
                $this->addFlash(
                    'info',
                    "le mot de passe courant saisi est incorrect"
                );
            }
        }

        return $this->render(
            'mmmfestBundle:Admin:settings.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    public function changeAccessAction($userId, $roles)
    {

        $em          = $this->getDoctrine()->getManager();
        $userManager = $em->getRepository('mmmfestBundle:User')->find(
            $userId
        );

        $userManager->setRoles(array($roles));
        $em->persist($userManager);
        $em->flush($userManager);


        return $this->redirectToRoute('team');
    }

    public function allOrganizationAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');

        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);

        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
            ->addSelect('?G ?P ?O')
            ->addWhere('?s','rdf:type','foaf:Organization','?G')
            ->addWhere('?s','?P','?O','?G')
            ->groupBy('?G ?P ?O');

        //$query    = 'SELECT ?G ?P ?O WHERE { GRAPH ?G {?S <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>  <http://xmlns.com/foaf/0.1/Organization> . ?S ?P ?O } } GROUP BY ?G ?P ?O ';
        $result   = $sfClient->sparql($sparql->getQuery());
        if (!is_array($result)) {
            $this->addFlash(
                'danger',
                'Une erreur s\'est produite lors de l\'affichage du formulaire'
            );

            return $this->redirectToRoute('home');
        }
        $result = $result["results"]["bindings"];
        $data   = [];
        foreach ($result as $value) {
            $data[$value["G"]["value"]][$value["P"]["value"]][] = $value["O"]["value"];
        }
        $data2 = [];
        $i     = 0;
        foreach ($data as $graph => $value) {
            $j = 0;
            foreach (mmmfestConfig::$organisationFields as $key) {
                if (array_key_exists($key, $data[$graph])) {
                    $transform = "";
                    foreach ($data[$graph][$key] as $temp) {
                        $transform .= $temp.'<br>';
                    }
                    $data2[$i][$j] = $transform." ";
                } else {
                    $data2[$i][$j] = "";
                }

                $j++;
            }
            $i++;
        }

        return $this->render(
            'mmmfestBundle:Admin:tab.html.twig',
            ["data" => $data2, "key" => mmmfestConfig::$organisationFields]

        );
    }

		public function foodRecapAction(){
				$title = [
					'lundi 4 octobre',
					'mardi 5 octobre',
					'mercredi 6 octobre',
					'jeudi 7 octobre',
					'vendredi 8 octobre',
					'samedi 9 octobre',
					'dimanche 10 octobre',
				];
				$userRepository = $this
					->getDoctrine()
					->getManager()
					->getRepository('mmmfestBundle:User');
				$users = $userRepository->findAll();
				$foodRecap = [];
				foreach ($users as $user){
						$userFood = json_decode($user->getRepas(),true);
						if(!empty($userFood) ){
								foreach ($userFood as $key => $dayFood){
										if(!array_key_exists($key-1,$foodRecap)){
												$foodRecap[$key-1][0] = 0;
												$foodRecap[$key-1][1] = 0;
												$foodRecap[$key-1][2] = 0;
										}
										$foodRecap[$key-1][0] = $foodRecap[$key-1][0] += (int)$dayFood[0] ;
										$foodRecap[$key-1][1] += (int)$dayFood[1];
										$foodRecap[$key-1][2] += (int)$dayFood[2];
								}
						}
				}
				return $this->render(
					'mmmfestBundle:Admin:foodRecap.html.twig',
					["foodRecap" => $foodRecap,"title"=>$title]
				);
		}
}
