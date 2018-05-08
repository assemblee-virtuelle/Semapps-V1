<?php

namespace semappsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Entity\User;
use semappsBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdministrationUserController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * Liste l'ensemble des utilisateurs présent dans la base de données SQL
     */
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
            $encryption = $this->get('semapps_bundle.encryption');
            // Generate password.
            $tokenGenerator = $this->get(
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
            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "L'utilisateur saisi existe déjà");

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
                $tabUserEnabled[$user->getId()]["organization"] = null;
            } else {
                $tabUserDisabled[$user->getId()]["username"] = $user->getUsername();
                $tabUserDisabled[$user->getId()]["email"] = $user->getEmail();
                $tabUserDisabled[$user->getId()]["organization"] = null;
                $tabUserDisabled[$user->getId()]["isResponsible"] = null;
            }

        }
        $inviteManager = $this->get('semapps_bundle.invite_manager');


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
                'userForm' => $form->createView(),
                'invite'    => $inviteManager->getCache(),
            )
        );
    }

    /**
     * @param $userId
     * @param string $nameRoute
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * (Re)Envoye le lien pour confirmer un compte utilisateur
     */
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
        $mailer = $this->get('semapps_bundle.event_listener.send_mail');
        $result = $mailer->sendConfirmMessage(
            $mailer::TYPE_USER,
            $user,
            $url
        );

        if ($result) {
            $this->addFlash('info', "Email envoyé pour l'utilisateur <b>" . $user->getUsername() . "</b> à l'adresse <b>" . $user->getEmail() . "</b>");
        }
        return $this->redirectToRoute($nameRoute);
    }

    /**
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Retire un compte utilisateur de la base SQL et le graph de l'uri de l'utilisateur SPARQL
     */
    public function removeUserAction($userId){

        $em = $this
            ->getDoctrine()
            ->getManager();
        $user = $em
            ->getRepository('semappsBundle:User')
            ->find($userId);

        $uri = $user->getSfLink();
        if($uri){
            $sfClient = $this->get('semantic_forms.client');
            $sparqlClient   = $this->get('sparqlbundle.client');

            $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);

            $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

            $sparql->addDelete('?S','?P','?O',$uri)
                ->addDelete('?SS','?PP','?S','?GR')
                ->addWhere('?S','?P','?O',$uri);

            $sfClient->update($sparql->getQuery());
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