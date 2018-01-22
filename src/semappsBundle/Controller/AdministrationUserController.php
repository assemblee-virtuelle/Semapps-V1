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

class AdministrationUserController extends Controller
{

    public function listUserAction(Request $request)
    {

        $form = $this->createForm(
            UserType::class,
            null,
            // Options.
            []
        );
        $form->add('organisation',EntityType::class, [
            'class' => 'semappsBundle:Organization',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.name', 'ASC');
            },
            'placeholder' =>'Choisir une organisation',
            'choice_label' => 'name',
            'required'   => false,
            'mapped'  => false,
        ]);
        $form->add(
            'access',
            HiddenType::class,
            ['data' => 'ROLE_MEMBER', 'mapped' =>false]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get posted data of type user
            $newUser = $form->getData();
            /** @var \semappsBundle\Services\Encryption $encryption */
            $encryption = $this->container->get('semappsBundle.encryption');
            // Generate password.
            /** @var TokenGenerator $tokenGenerator */
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $newUser->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $newUser->setSfUser($encryption->encrypt($randomPassword));

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $newUser->setConfirmationToken($conf_token);

            //Set the roles
            $newUser->addRole($form->get('access')->getData());
            $newUser->setFkOrganisation($form->get('organisation')->getData()->getId());
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "l'utilisateur saisi existe déjà");

                return $this->redirectToRoute('userList');
            }
        }
        $users = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:User')
            ->findAll();

        $tabUserEnabled = $tabUserDisabled = [];
        /** @var User $user */
        foreach ($users as $user){
            $organization = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('semappsBundle:Organization')
                ->findOneBy(array('id' => $user->getFkOrganisation()));

            if($user->isEnabled()){

                $tabUserEnabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserEnabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserEnabled[$user->getId()]["lastLogin"] = $user->getLastLogin();
                $tabUserEnabled[$user->getId()]["organization"] =($organization)? $organization->getName():null;
            }
            else{
                $tabUserDisabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserDisabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserDisabled[$user->getId()]["organization"] = ($organization)? $organization->getName():null;
                $tabUserDisabled[$user->getId()]["isResponsible"] = ($organization && $organization->getFkResponsable() == $user->getId())? true:false;
            }

        }

        return $this->render(
            'semappsBundle:Admin:listUser.html.twig',
            array(
                'userEnabled'      => $tabUserEnabled,
                'userDisabled'     => $tabUserDisabled,
                'nameRoute'        => 'userList',
                'usersRolesLabels' => [
                    'ROLE_SUPER_ADMIN' => 'Super admin',
                    'ROLE_ADMIN'       => 'Administration',
                    'ROLE_MEMBER'      => 'Member',
                ],
                'userForm'					=>$form->createView()
            )
        );
    }

    public function sendUserAction($userId,$nameRoute = 'team'){
        $user = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:User')
            ->find($userId);

        $url = $this->generateUrl(
            'fos_user_registration_confirm',
            array('token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $organisation = ($user->getFkOrganisation())?$this
            ->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:Organization')
            ->find($user->getFkOrganisation()) : null;
        //send email to the new user
        $mailer = $this->get('semappsBundle.EventListener.SendMail');
        $result = $mailer->sendConfirmMessage(
            ($organisation != null && $user->getId() == $organisation->getFkResponsable()) ? $mailer::TYPE_RESPONSIBLE : $mailer::TYPE_USER,
            $user,
            $organisation,
            $url
        );

        if($result){
            $this->addFlash('info',"email envoyé pour l'utilisateur <b>".$user->getUsername()."</b> à l'adresse <b>".$user->getEmail()."</b>");
        }
        return $this->redirectToRoute($nameRoute);
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
        /** @var SparqlRepository $sparqlRepository */
        $sparqlRepository   = $this->container->get('semappsBundle.sparqlRepository');
        // Find all users.
        $userManager         = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'semappsBundle:User'
            );
        $organisationManager = $this->getDoctrine()
            ->getManager()
            ->getRepository(
                'semappsBundle:Organization'
            );
        $users               = $userManager->findBy(
            array('fkOrganisation' => $this->getUser()->getFkOrganisation())
        );
        $organisation       = $organisationManager->find(
            $this->getUser()->getFkOrganisation()
        );
        /** @var Form $form */
        $form                = $this->get('form.factory')->create(
            UserType::class
        );
        $idResponsible = $organisation->getFkResponsable();

        // using the field username_canonical to have the name and forname of each user
        /** @var User $user */
        foreach ($users as $user){
            //TODO make function to find something about someone
            /** @var sparqlSelect $sparql */
            $sparql = $sparqlRepository->newQuery($sparqlRepository::SPARQL_SELECT);
            $sparql->addPrefixes($sparql->prefixes)
                ->addPrefix('pair','http://virtual-assembly.org/pair#')
                ->addSelect('?name')
                ->addSelect('?forname')
                ->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
                    'pair:lastName',
                    '?name',
                    $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL))
                ->addOptional($sparql->formatValue($user->getSfLink(),$sparql::VALUE_TYPE_URL),
                    'pair:firstName',
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
            $newUser = $form->getData();
            /** @var \semappsBundle\Services\Encryption $encryption */
            $encryption = $this->container->get('semappsBundle.encryption');
            // Generate password.
            /** @var TokenGenerator $tokenGenerator */
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $newUser->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $newUser->setSfUser($encryption->encrypt($randomPassword));

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $newUser->setConfirmationToken($conf_token);

            //Set the roles
            $newUser->addRole($form->get('access')->getData());

            $newUser->setFkOrganisation($this->getUser()->getFkOrganisation());
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
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
            $mailer =  $this->get('semappsBundle.EventListener.SendMail');
            $result =$mailer->sendConfirmMessage(
                $mailer::TYPE_USER,
                $newUser,
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
                    $newUser->getUsername().
                    '</b>. Un email a été envoyé à <b>'.
                    $newUser->getEmail().
                    '</b> pour lui communiquer ses informations de connexion.'
                );
            }else{
                $this->addFlash(
                    'danger',
                    'Un compte à bien été créé pour <b>'.
                    $newUser->getUsername().
                    "</b>. mais l'email n'est pas parti à l'adresse <b>".
                    $newUser->getEmail().
                    '</b>'
                );
            }

            // Go back to team page.
            return $this->redirectToRoute('team');
        }

        return $this->render(
            'semappsBundle:Admin:team.html.twig',
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

    public function changeAccessAction($userId, $roles)
    {

        $em          = $this->getDoctrine()->getManager();
        $userManager = $em->getRepository('semappsBundle:User')->find(
            $userId
        );

        $userManager->setRoles(array($roles));
        $em->persist($userManager);
        $em->flush($userManager);


        return $this->redirectToRoute('team');
    }
}
