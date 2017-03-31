<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Form\ProfileType;
use GrandsVoisinsBundle\Form\UserType;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GrandsVoisinsBundle\Form\AdminSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{

    public function homeAction()
    {
        return $this->redirectToRoute('profile');
    }

    public function profileAction(Request $request)
    {
        /** @var $user \GrandsVoisinsBundle\Entity\User */
        $user           = $this->getUser();
        $userSfLink     = $user->getSfLink();
        $sfClient       = $this->container->get('semantic_forms.client');
        $oldPictureName = $user->getPictureName();

        $organisation = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('GrandsVoisinsBundle:Organisation')
          ->find($this->getUser()->getFkOrganisation());

        // Build main form.
        $options = [
          'login'    => $user->getEmail(),
          'password' => $user->getSfUser(),
          'graphURI' => $organisation->getGraphURI(),
          'client'   => $sfClient,
          'spec'     => SemanticFormsClient::PERSON,
          'aliases'  => array_flip(
            GrandsVoisinsConfig::$fieldsAliasesProfile
          ),
          'values'   => $userSfLink,
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

            // Rebuild links between related reference fields.
            // TODO Rewrite for all kind of refereced fields and without using $_POST data.
            /*if ($organisation->getSfOrganisation()) {
                $sfClient
                  ->verifMember(
                    $_POST,
                    $_POST["graphURI"],
                    $organisation->getSfOrganisation(),
                    $_POST["uri"]
                  );
            }*/

            // Manage picture.
            $newPicture = $user->getPictureName();
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
                $user->setPictureName(
                  $fileUploader->upload($newPicture)
                );
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
            }

            $this->addFlash(
              'success',
              'Votre profil a bien été mis à jour.'
            );

            return $this->redirectToRoute('profile');
        }

        // Fill form
        return $this->render(
          'GrandsVoisinsBundle:Admin:profile.html.twig',
          array(
            'form'      => $form->createView(),
              // 'picture'   => $picture->createView(),
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
        // Find all users.
        $userManager = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:User'
        );
        $organisationManager = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );
        $users       = $userManager->findBy(
          array('fkOrganisation' => $this->getUser()->getFkOrganisation())
        );
        $idResponsible = $organisationManager->find($this->getUser()->getFkOrganisation())->getFkResponsable();
        $form = $this->get('form.factory')->create(UserType::class);

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
                GrandsVoisinsConfig::TEAM,
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
            'users'              => $users,
            'idResponsable'      => $idResponsible,
            'usersRolesLabels'   => [
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
        $query    = 'SELECT ?G ?P ?O WHERE { GRAPH ?G {?S <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>  <http://xmlns.com/foaf/0.1/Organization> . ?S ?P ?O } } GROUP BY ?G ?P ?O ';
        $result   = $sfClient->sparql($query);
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
