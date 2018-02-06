<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\UserBundle\Util\TokenGenerator;
use semappsBundle\Entity\User;
use semappsBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $form->add(
            'access',
            HiddenType::class,
            ['data' => 'ROLE_MEMBER', 'mapped' => false]
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
            //$newUser->setFkOrganisation($form->get('organisation')->getData()->getId());
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
        foreach ($users as $user) {

            if ($user->isEnabled()) {

                $tabUserEnabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserEnabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserEnabled[$user->getId()]["lastLogin"] = $user->getLastLogin();
                $tabUserEnabled[$user->getId()]["organization"] = null;//($organization)? $organization->getName():null;
            } else {
                $tabUserDisabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserDisabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserDisabled[$user->getId()]["organization"] = null;//($organization)? $organization->getName():null;
                $tabUserDisabled[$user->getId()]["isResponsible"] = null; // ($organization && $organization->getFkResponsable() == $user->getId())? true:false;
            }

        }

        return $this->render(
            'semappsBundle:Admin:listUser.html.twig',
            array(
                'userEnabled' => $tabUserEnabled,
                'userDisabled' => $tabUserDisabled,
                'nameRoute' => 'userList',
                'usersRolesLabels' => [
                    'ROLE_SUPER_ADMIN' => 'Super admin',
                    'ROLE_ADMIN' => 'Administration',
                    'ROLE_MEMBER' => 'Member',
                ],
                'userForm' => $form->createView()
            )
        );
    }

    public function sendUserAction($userId, $nameRoute = 'sendUser')
    {
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
        //send email to the new user
        $mailer = $this->get('semappsBundle.EventListener.SendMail');
        $result = $mailer->sendConfirmMessage(
            $mailer::TYPE_USER,//($organisation != null && $user->getId() == $organisation->getFkResponsable()) ? $mailer::TYPE_RESPONSIBLE : $mailer::TYPE_USER,
            $user,
            null,
            $url
        );

        if ($result) {
            $this->addFlash('info', "email envoyé pour l'utilisateur <b>" . $user->getUsername() . "</b> à l'adresse <b>" . $user->getEmail() . "</b>");
        }
        return $this->redirectToRoute($nameRoute);
    }

    public function removeUserAction($userId){

        $em = $this
            ->getDoctrine()
            ->getManager();
        $user = $em
            ->getRepository('semappsBundle:User')
            ->find($userId);

        $uri = $user->getSfLink();
        if($uri){
            /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
            $sfClient = $this->container->get('semantic_forms.client');
            /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
            $sparqlClient   = $this->container->get('sparqlbundle.client');

            $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
//            $sparqlDeux = clone $sparql;

            $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

            $sparql->addDelete('?S','?P','?O',$uri)
                ->addDelete('?SS','?PP','?S','?GR')
                ->addWhere('?S','?P','?O',$uri);

            $sfClient->update($sparql->getQuery());
//            $sfClient->update($sparqlDeux->getQuery());
        }
        $em->remove($user);
        try{
            $em->flush();
            $this->addFlash("success", "Utilisateur supprimé !");
        }catch (Exception $e){
            $this->addFlash("info", "Problème lors de la suppression");
        }
        return $this->redirectToRoute('userList');

    }
}