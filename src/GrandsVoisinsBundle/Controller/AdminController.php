<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Form\ProfileType;
use GrandsVoisinsBundle\Form\RegisterType;
use GrandsVoisinsBundle\Form\UserType;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GrandsVoisinsBundle\Form\AdminSettings;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        //get all organization
        $organisations = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:Organisation')
          ->findAll();

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

            $data->setSfUser($form->get('password')->getData());

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $data->setConfirmationToken($conf_token);

            //Set the roles
            $data->addRole('ROLE_MEMBER');

            $data->setFkOrganisation($form->get('organisation')->getData());
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "l'utilisateur saisi existe déjà");

                return $this->redirectToRoute('register');
            }
            $this->addFlash('success','Votre demande de compte a bien été enregistrée');
            return $this->redirectToRoute('fos_user_security_login');
        }
        // Fill form
        return $this->render(
          'GrandsVoisinsBundle:Admin:register.html.twig',
          array(
            'form'      => $form->createView(),
          )
        );
    }

    public function listUserAction()
    {

        $userRepository = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:User');

        $userEnabled = $userRepository->findBy(array('enabled' => 1));
        $userDisabled = $userRepository->findBy(array('enabled' => 0));
        foreach ($userDisabled as $user){
            $user->setFkOrganisation($this
              ->getDoctrine()
              ->getManager()
              ->getRepository('GrandsVoisinsBundle:Organisation')
              ->findOneBy(array('id' => $user->getFkOrganisation()))
              ->getName());
        }
        foreach ($userEnabled as $user){
            $user->setFkOrganisation($this
              ->getDoctrine()
              ->getManager()
              ->getRepository('GrandsVoisinsBundle:Organisation')
              ->findOneBy(array('id' => $user->getFkOrganisation()))
              ->getName());
        }
        return $this->render(
          'GrandsVoisinsBundle:Admin:listUser.html.twig',
          array(
            'userEnabled'      => $userEnabled,
            'userDisabled'     => $userDisabled,
            'usersRolesLabels' => [
              'ROLE_SUPER_ADMIN' => 'Super admin',
              'ROLE_ADMIN'       => 'Administration',
              'ROLE_MEMBER'      => 'Member',
            ],
          )
        );


    }

    public function sendUserAction($userId){
        $user = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:User')
          ->findOneBy(['id' =>$userId]);
        $url = $this->generateUrl(
          'fos_user_registration_confirm',
          array('token' => $user->getConfirmationToken()),
          UrlGeneratorInterface::ABSOLUTE_URL
        );
        //send email to the new user

        $this->get('GrandsVoisinsBundle.EventListener.SendMail')
          ->sendConfirmMessage(
            $user,
            $url,
            $user->getSfUser()
          );
        $this->addFlash('info',"email envoyé pour l'utilisateur <b>".$user->getUsername()."</b> à l'adresse <b>".$user->getEmail()."</b>");
        return $this->redirectToRoute('user');
    }

    public function profileAction(Request $request)
    {
        /** @var $user \GrandsVoisinsBundle\Entity\User */
        $user           = $this->getUser();
        $userSfLink     = $user->getSfLink();
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient       = $this->container->get('semantic_forms.client');
        /** @var \AV\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
        $oldPictureName = $user->getPictureName();
        //$predicatImage  = $this->getParameter('semantic_forms.fields_aliases')['image'];
        $organisation = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('GrandsVoisinsBundle:Organisation')
            ->find($this->getUser()->getFkOrganisation());

        // Build main form.
        $options = [
            'login'                 => $user->getEmail(),
            'password'              => $user->getSfUser(),
            'graphURI'              => $organisation->getGraphURI(),
            'client'                => $sfClient,
            'spec'                  => SemanticFormsClient::SPEC_PERSON,
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
                $fileUploader = $this->get('GrandsVoisinsBundle.fileUploader');
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
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addDelete(
                  $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                  'foaf:img',
                  '?o',
                  $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sparql->addWhere(
                  $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                  'foaf:img',
                  '?o',
                  $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sfClient->update($sparql->getQuery());

                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addInsert(
                  $sparql->formatValue($userSfLink, $sparql::VALUE_TYPE_URL),
                  'foaf:img',
                  $sparql->formatValue($fileUploader->generateUrlForFile($user->getPictureName()),$sparql::VALUE_TYPE_URL),
                  $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));
                $sfClient->update($sparql->getQuery());

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
                    ->getRepository('GrandsVoisinsBundle:User');

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
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addInsert($uriOrgaFormatted,'org:hasMember',$uripersonFormatted,$graphFormatted);
                //dump($sparql->getQuery());
                $sfClient->update($sparql->getQuery());
                //memberOf
                $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
                $sparql->addPrefixes($sparql->prefixes);
                $sparql->addInsert($uripersonFormatted,'org:memberOf',$uriOrgaFormatted,$graphFormatted);
                //dump($sparql->getQuery());
                $sfClient->update($sparql->getQuery());

            }

            $this->addFlash(
                'success',
                'Votre profil a bien été mis à jour.'
            );

            return $this->redirectToRoute('fos_user_profile_show');
        }

        // Fill form
        return $this->render(
            'GrandsVoisinsBundle:Admin:profile.html.twig',
            array(
                'form'      => $form->createView(),
                'entityUri' => $userSfLink,
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
        /** @var \AV\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
        // Find all users.
        $userManager         = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'GrandsVoisinsBundle:User'
            );
        $organisationManager = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'GrandsVoisinsBundle:Organisation'
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
            $sparql->addPrefixes($sparql->prefixes);
            $sparql->addSelect('?name');
            $sparql->addSelect('?forname');
            $sparql->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
              'foaf:familyName',
              '?name',
              $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL));

            $sparql->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
              'foaf:givenName',
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
//            //nom
//            $query = $sfClient->prefixesCompiled . "\n SELECT ?o WHERE { GRAPH <".$organisation->getGraphURI()."> { <".$user->getSfLink()."> foaf:familyName ?o. }} ";
//            $result = $sfClient->sparql($query);
//            if (array_key_exists(0,$result["results"]["bindings"]))
//                $user->setUsernameCanonical($result["results"]["bindings"][0]['o']["value"]);
//            else
//                $user->setUsernameCanonical("");
//            //prenom
//            $query = $sfClient->prefixesCompiled . "\n SELECT ?o WHERE { GRAPH <".$organisation->getGraphURI()."> { <".$user->getSfLink()."> foaf:givenName ?o. }} ";
//            $result = $sfClient->sparql($query);
//            if (array_key_exists(0,$result["results"]["bindings"]))
//                $user->setUsernameCanonical($user->getUsernameCanonical().' '.$result["results"]["bindings"][0]['o']["value"]);

        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get posted data of type user
            $data = $form->getData();

            // Generate password.
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $data->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $data->setSfUser($randomPassword);

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
            $this->get('GrandsVoisinsBundle.EventListener.SendMail')
                ->sendConfirmMessage(
                    $data,
                    $url,
                    $randomPassword
                );

            // TODO Grant permission to edit same organisation as current user.
            // Display message.
            $this->addFlash(
                'success',
                'Un compte à bien été créé pour <b>'.
                $data->getUsername().
                '</b>. Un email a été envoyé à <b>'.
                $data->getEmail().
                '</b> pour lui communiquer ses informations de connexion.'
            );

            // Go back to team page.
            return $this->redirectToRoute('team');
        }

        return $this->render(
            'GrandsVoisinsBundle:Admin:team.html.twig',
            array(
                'users'            => $users,
                'idResponsable'    => $idResponsible,
                'usersRolesLabels' => [
                    'ROLE_SUPER_ADMIN' => 'Super admin',
                    'ROLE_ADMIN'       => 'Administration',
                    'ROLE_MEMBER'      => 'Member',
                ],
                'formAddUser'      => $form->createView(),
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
            'GrandsVoisinsBundle:Admin:settings.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    public function changeAccessAction($userId, $roles)
    {

        $em          = $this->getDoctrine()->getManager();
        $userManager = $em->getRepository('GrandsVoisinsBundle:User')->find(
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

        /** @var \AV\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);

        $sparql->addPrefixes($sparql->prefixes);
        $sparql->addSelect('?G ?P ?O');
        $sparql->addWhere('?s','rdf:type','foaf:Organization','?G');
        $sparql->addWhere('?s','?P','?O','?G');
        $sparql->groupBy('?G ?P ?O');

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
            foreach (GrandsVoisinsConfig::$organisationFields as $key) {
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
            'GrandsVoisinsBundle:Admin:tab.html.twig',
            ["data" => $data2, "key" => GrandsVoisinsConfig::$organisationFields]

        );
    }
}
