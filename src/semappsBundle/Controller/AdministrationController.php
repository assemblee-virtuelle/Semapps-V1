<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Util\TokenGenerator;
use semappsBundle\Entity\User;
use semappsBundle\Form\ImportType;
use semappsBundle\Form\RegisterType;
use semappsBundle\Form\UserType;
use semappsBundle\Repository\UserRepository;
use semappsBundle\Form\AdminSettings;
use semappsBundle\semappsConfig;
use semappsBundle\Services\contextManager;
use semappsBundle\Services\SparqlRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;
use VirtualAssembly\SparqlBundle\Sparql\sparqlSelect;

class AdministrationController extends Controller
{

    public function registerAction(Request $request)
    {
        /** @var \semappsBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('semappsBundle.encryption');
        /** @var UserRepository $userRepository */
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:User');
        //get all organization
//        $organisationRepository =  $this
//            ->getDoctrine()
//            ->getManager()
//            ->getRepository('semappsBundle:Organization');
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
            /** @var TokenGenerator $tokenGenerator */
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $newUser->setPassword(
                password_hash($form->get('password')->getData(), PASSWORD_BCRYPT, ['cost' => 13])
            );

            $newUser->setSfUser($encryption->encrypt($form->get('password')->getData()));

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $newUser->setConfirmationToken($conf_token);

            //Set the roles
            $newUser->addRole('ROLE_MEMBER');
//            if(!is_null($form->get('organisation')->getData()))
//                $organisationId = $form->get('organisation')->getData()->getId();
//            else
//                $organisationId = $form->get('organisation')->getData();

            //$newUser->setFkOrganisation($organisationId);

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "l'utilisateur saisi existe déjà, vous pouvez essayer de réinitialiser votre mot de passe en renseignant votre e-mail ou votre login");

                return $this->redirectToRoute('fos_user_resetting_request',array('email' => $newUser->getEmail()));
            }
            $this->addFlash('success','Merci à toi cher Voisin, nous avons bien pris en compte ton inscription,
             nous allons la valider dans les prochaines heures, après quoi tu recevras un mail de confirmation :-) A très bientôt sur la carto ! ');

            //notification
            $usersSuperAdmin = $userRepository->getSuperAdminUsers();
            $listOfEmail= [];
            //$organisation=null;
//            if($organisationId){
//                $organisation = $organisationRepository->find($form->get('organisation')->getData());
//                $responsible = $userRepository->findOneBy(['fkOrganisation' => $form->get('organisation')->getData()]);
//                array_push($listOfEmail,$responsible->getEmail());
//            }

            foreach ($usersSuperAdmin as $superuser){
                array_push($listOfEmail,$superuser["email"]);
            }
            $mailer = $this->get('semappsBundle.EventListener.SendMail');
            $mailer->sendNotification($mailer::TYPE_NOTIFICATION,$newUser,null,array_unique($listOfEmail));

            return $this->redirectToRoute('fos_user_security_login');
        }
        // Fill form
        return $this->render(
            'semappsBundle:Admin:register.html.twig',
            array(
                'form'      => $form->createView(),
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
}
