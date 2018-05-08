<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Form\RegisterType;
use semappsBundle\Repository\UserRepository;
use semappsBundle\Form\AdminSettings;
use semappsBundle\Services\ContextManager;
use semappsBundle\Services\InviteManager;
use semappsBundle\Services\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class AdministrationController extends Controller
{
    /**
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Gère le formulaire d'enregistrement d'une personne. Cette page est disponible uniquement pour les personnes ayant été invité à se créer un compte.
     * Le token est la clé d'accès à cette page.
     * @see InviteManager
     * @see inviteAction
     */
    public function registerAction(Request $request,$token)
    {
        //To encrypt the password
        $encryption = $this->get('semapps_bundle.encryption');
        //the cache who manage invitation
        $inviteManager = $this->get('semapps_bundle.invite_manager');

        if($this->getUser()) {
            $this->addFlash('info', "Vous devez vous déconnecter avant d'accéder à cette page");
            return $this->redirectToRoute("home");
        }

        // check if the invitation is correct
        $email = $inviteManager->verifyInvite($token);
        if(!$email){
            $this->addFlash('info', "Token non reconnu");
            return $this->redirectToRoute("fos_user_security_login");
        }

        //get the form
        $form = $this->createForm(
            RegisterType::class,
            null,
            // Options.
            []
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //create a new user
            $newUser = $form->getData();

            $newUser->setPassword(
                password_hash($form->get('password')->getData(), PASSWORD_BCRYPT, ['cost' => 13])
            );

            $newUser->setSfUser($encryption->encrypt($form->get('password')->getData()));

            //Set the roles
            $newUser->addRole('ROLE_MEMBER');

            $newUser->setEnabled(true);

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "L'utilisateur saisi existe déjà, vous pouvez essayer de réinitialiser votre mot de passe en renseignant votre e-mail ou votre login");

                return $this->redirectToRoute('fos_user_resetting_request',array('email' => $newUser->getEmail()));
            }
            $this->addFlash('success', 'Votre compte est maintenant créé, vous pouvez vous connecter et commencer à remplir votre profil !');


            return $this->redirectToRoute('fos_user_security_login');
        }
        // Fill form
        return $this->render(
            'semappsBundle:Admin:register.html.twig',
            array(
                'form'      => $form->createView(),
                'email'     => ($email)? $email : null,
            )
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Gère le changement de mot de passe et de username.
     */
    public function settingsAction(Request $request)
    {
        $user = $this->GetUser();
        /** @var Form $form */
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
                            "Les mots de passe saisis ne correspondent pas"
                        );
                    }
                }
                $em->persist($user);
                try {
                    if ($isOK) {
                        $em->flush();
                        $this->addFlash(
                            "success",
                            "Les informations ont été correctement enregistrées"
                        );
                    }

                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        "Le nom d'utilisateur saisi existe déjà"
                    );

                    return $this->redirectToRoute('settings');
                }
            } else {
                $this->addFlash(
                    'info',
                    "Le mot de passe courant saisi est incorrect"
                );
            }
        }

        return $this->render(
            'semappsBundle:Admin:settings.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    /**
     * @param null $context
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Change le contexte (correspond au menu "éditer en tant que" partie admin).
     * Si null -> contexte de la personne
     * Si !null -> contexte d'une organisation
     * @see ContextManager
     */
    public function changeContextAction($context =null){
        $contextManager = $this->get('semapps_bundle.context_manager');
        $contextManager->setContext($this->getUser()->getSfLink(),urldecode($context));
        $this->addFlash('success',"Le contexte a bien été changé");
        return $this->redirectToRoute('personComponentFormWithoutId',['uniqueComponentName' =>'person']);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * Gère le formulaire et l'envoi du mail d'invitation.
     * Pour le mail d'invitation,le service inviteManager génère un token qui sera placé dans l'url pour accéder à l'action register
     * @see registerAction
     * @see InviteManager
     */
    public function inviteAction(Request $request){
        $form = $this->createFormBuilder(null)
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class, array('label' => 'Envoyer une invitation'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            /** @var UserRepository $userRepository */
            $userRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('semappsBundle:User');

            $inviteManager = $this->get('semapps_bundle.invite_manager');

            $email = $form->get('email')->getData();

            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user){
                $this->addFlash("info","L'email existe déjà");
                return $this->redirectToRoute('invite');
            }

            $token= $inviteManager->newInvite($email);

            $this->sendEmailInvitation($email,$token);

            $this->addFlash('success', "Email envoyé à l'adresse <b>" . $email . "</b> !");
        }
        return $this->render(
            'semappsBundle:Admin:invite.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param $email
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Relance le mail d'invitation
     */
    public function sendInviteAction($email,$token){
        $this->sendEmailInvitation($email,$token);
        $this->addFlash('success', "Email envoyé à l'adresse <b>" . $email . "</b> !");
        return $this->redirectToRoute('userList');
    }

    /**
     * @param $email
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Supprime une invitation. Retire le token dans l'invite manager
     * @see InviteManager
     */
    public function deleteInviteAction($email){
        $inviteManager = $this->get('semapps_bundle.invite_manager');
        $inviteManager->removeInvite($email);
        return $this->redirectToRoute('userList');
    }

    /**
     * @param $email
     * @param $token
     * Envoie le mail d'invitation
     * @see Mailer
     * TODO : déplacer cette fonction dans Mailer
     */
    private function sendEmailInvitation($email,$token){
        $mailer = $this->get('semapps_bundle.event_listener.send_mail');
        $website = $this->getParameter('carto.domain');
        $url = "http://".$website.'/register/'.$token;
        $sujet = "[".$website."] Vous avez reçu une invitation !";
        $content= "Bonjour ".$email." !<br><br> 
                        L'utilisateur ".$this->getUser()->getEmail(). " vous a invité à vous créer un compte sur le site ".$website." !<br><br>
                        Pour créer votre compte sur la plateforme, veuillez <a href='".$url."'>cliquer ici</a> <br>
                        Si le lien ne fonctionne pas, veuillez copier-coller ce lien dans un navigateur : <br>".$url."<br><br>
                        <br>
                        A très bientôt :-)";
        $mailer->sendMessage($email,$sujet,$content);
    }
}
