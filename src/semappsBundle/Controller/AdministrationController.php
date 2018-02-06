<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Form\RegisterType;
use semappsBundle\Repository\UserRepository;
use semappsBundle\Form\AdminSettings;
use semappsBundle\Services\contextManager;
use semappsBundle\Services\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class AdministrationController extends Controller
{

    public function registerAction(Request $request,$token)
    {
        /** @var \semappsBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('semappsBundle.encryption');
        /** @var \semappsBundle\Services\InviteManager $inviteManager */
        $inviteManager = $this->container->get('semappsBundle.invitemanager');


        if($this->getUser()) {
            $this->addFlash('info', "Vous devez vous déconnecter avant d'accéder à cette page");
            return $this->redirectToRoute("home");
        }

        // voter pour le token
        $email = $inviteManager->verifyInvite($token);
        if(!$email){
            $this->addFlash('info', "token non reconnu");
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
                $this->addFlash('danger', "l'utilisateur saisi existe déjà, vous pouvez essayer de réinitialiser votre mot de passe en renseignant votre e-mail ou votre login");

                return $this->redirectToRoute('fos_user_resetting_request',array('email' => $newUser->getEmail()));
            }
            $this->addFlash('success', 'votre compte est maintenant créé, vous pouvez vous connecter et commencer à remplir votre profil !');


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
            'semappsBundle:Admin:settings.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    public function changeContextAction($context =null){
        /** @var contextManager $contextManager */
        $contextManager = $this->container->get('semappsBundle.contextManager');
        $contextManager->setContext($this->getUser()->getSfLink(),urldecode($context));
        $this->addFlash('success',"le contexte a bien été changé");
        return $this->redirectToRoute('personComponentFormWithoutId',['uniqueComponentName' =>'person']);
    }

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

            /** @var \semappsBundle\Services\InviteManager $inviteManager */
            $inviteManager = $this->container->get('semappsBundle.invitemanager');
            /** @var Mailer $mailer */
            $mailer = $this->container->get('semappsBundle.EventListener.SendMail');
            $email = $form->get('email')->getData();

            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user){
                $this->addFlash("info","l'email existe déjà");
                return $this->redirectToRoute('invite');
            }

            $token= $inviteManager->newInvite($email);

            $website = $this->getParameter('carto.domain');
            $url = "http://".$website.'/register/'.$token;
            $sujet = "[".$website."] Vous avez recu une invitation !";
            $content= "Bonjour ".$email." !<br><br> 
                        L'utilisateur ".$this->getUser()->getEmail(). " vous a invité à vous créer un compte sur le site ".$website." !<br><br>
                        Pour créer votre compte sur la plateforme, veuillez <a href='".$url."'>cliquer ici</a> <br><br>
                        A très bientôt :-)";
            $mailer->sendMessage($email,$sujet,$content);
            $this->addFlash('success', "Email envoyé à l'adresse <b>" . $email . "</b> !");
        }
        return $this->render(
            'semappsBundle:Admin:invite.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

}
