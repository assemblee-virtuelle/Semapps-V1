<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Form\UserType;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use GrandsVoisinsBundle\Form\AdminSettings;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{

    public function homeAction()
    {
        return $this->redirectToRoute('profile');
    }

    public function profileAction(Request $request)
    {
        $user       = $this->GetUser();
        $userSfLink = $this->getUser()->getSfLink();
        $sfClient   = $this->container->get('semantic_forms.client');

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'GrandsVoisinsBundle:Organisation'
        );

        $organisation = $organisationEntity->find(
          $this->getUser()->getFkOrganisation()
        );
        if (!$userSfLink) {
            $form = $sfClient->create(SemanticFormsClient::PERSON);
        } else {
            $form = $sfClient->edit(
              $userSfLink,
              SemanticFormsClient::PERSON
            );
        }
        if (!$form) {
            $this->addFlash(
              'danger',
              'Une erreur s\'est produite lors de l\'affichage du formulaire'
            );

            return $this->redirectToRoute('profile');
        }

        $picture = $this->createFormBuilder($user)
          ->add('pictureName', FileType::class, array('data_class' => null))
          ->add(
            'oldPicture',
            HiddenType::class,
            array('mapped' => false, 'data' => $user->getPictureName())
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
            $user->setPictureName(
              $this->get('GrandsVoisinsBundle.fileUploader')->upload(
                $user->getPictureName()
              )
            );
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render(
          'GrandsVoisinsBundle:Admin:profile.html.twig',
          array(
            "form"       => $form,
            "graphURI"   => $organisation->getGraphURI(),
            'picture'    => $picture->createView(),
            'urlPicture' => ($user->getPictureName(
            )) ? 'http://'.$request->getHost().':'.$request->getPort(
              ).'/uploads/pictures/'.$user->getPictureName() : null,
          )
        );
    }

    public function profileSaveAction()
    {
        $info = $this
          ->container
          ->get('semantic_forms.client')
          ->send(
            $_POST,
            $this->getUser()->getEmail(),
            $this->getUser()->getSfUser()
          );

        if ($info == 200) {
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
              ->setParameter('link', $_POST["uri"])
              ->setParameter('id', $this->getUser()->getId())
              ->getQuery()
              ->execute();

            $this->addFlash(
              'success',
              'Votre profil a bien été mis à jour.'
            );

            return $this->redirectToRoute('profile');
        } else {
            $this->addFlash(
              'success',
              'Une erreur s\'est produite. Merci de contacter l\'administrateur du site <a href="mailto:romain.weeger@wexample.com">romain.weeger@wexample.com</a>.'
            );
        }

        return $this->redirectToRoute('profile');
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
        $users       = $userManager->findBy(
          array('fkOrganisation' => $this->getUser()->getFkOrganisation())
        );

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
            'users'            => $users,
            'usersRolesLabels' => [
              'ROLE_SUPER_ADMIN' => 'Super admin',
              'ROLE_ADMIN'       => 'Administration',
              'ROLE_EDITOR'      => 'Editeur',
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
}
