<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\Form\OrganisationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrganisationController extends Controller
{
    public function homeAction(Request $request)
    {

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository('GrandsVoisinsBundle:Organisation');
        $organisations = $organisationEntity->findAll();

        //form pour l'organisation
        $organisation = new Organisation();
        $form = $this->get('form.factory')->create(OrganisationType::class,$organisation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //for the organisation
            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);

            // actually executes the queries (i.e. the INSERT query)
            $em->flush($organisation);

            //TODO find a way to call teamAction in admin
            //for the user
            $user = new User();

            $user->setUsername($form->get('username')->getData());
            $user->setEmail($form->get('email')->getData());

            // Generate password.
            $tokenGenerator = $this->container->get(
                'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $user->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            $user->setSfUser($randomPassword);

            // Generate the token for the confirmation email
            $conf_token = $tokenGenerator->generateToken();
            $user->setConfirmationToken($conf_token);

            //Set the roles
            $user->addRole("ROLE_ADMIN");

            $user->setFkOrganisation($organisation->getId());

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //send the email for the new user
            $message = \Swift_Message::newInstance()
                ->setSubject('bonjour ' . $user->getUsername())
                ->setFrom('seb.mail.symfony@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Bonjour " . $user->getUsername() . " !<br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur http://localhost:8000/register/confirm/" . $conf_token . ".<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : " . $user->getUsername() . "<br>
                    Mot de passe : " . $randomPassword . "<br><br>
                    Cordialement,
                    L'équipe"
                    ,
                    'text/html'
                );
            $this->get('mailer')->send($message);

            // TODO Grant permission to edit same organisation as current user.

            // Display message.
            $this->addFlash(
                'success',
                'Un compte à bien été créé pour <b>' .
                $user->getUsername() .
                '</b>. Un email a été envoyé à <b>' .
                $user->getEmail() .
                '</b> pour lui communiquer ses informations de connexion.'
            );
            return $this->redirectToRoute('home_orga');
        }
        return $this->render('GrandsVoisinsBundle:Organisation:home.html.twig', array(
            "organisations" => $organisations,
            "formAddOrganisation" => $form->createView(),
        ));
    }

    public function newAction()
    {
        return $this->render('GrandsVoisinsBundle:Organisation:new.html.twig', array(
            // ...
        ));
    }

}
