<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\Form\OrganisationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrganisationController extends Controller
{
    public function allAction(Request $request)
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

            // send email to th new organization
            $body=  "Bonjour ".$user->getUsername()." !<br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur http://localhost:8000/register/confirm/".$conf_token.".<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : ".$user->getUsername()."<br>
                    Mot de passe : ".$randomPassword."<br><br>
                    Cordialement,
                    L'équipe";
            $this->get('GrandsVoisinsBundle.EventListener.SendMail')->sendConfirmMessage($user, $body);

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
            return $this->redirectToRoute('all_orga');
        }
        return $this->render('GrandsVoisinsBundle:Organisation:home.html.twig', array(
            "organisations" => $organisations,
            "formAddOrganisation" => $form->createView(),
        ));
    }

    public function newOrganisationAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');
        // questionner la base pour savoir si l'orga est deja créer
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );
        $organisation = $organisationEntity->findOneById(
            $this->GetUser()->getFkOrganisation()
        );
        if (is_null($organisation->getSfOrganisation())){
            $json = $sfClient->createFoaf('Organization');
            $edit = false;
        }

        else{
            $json = $sfClient->getForm($organisation->getSfOrganisation());
            $edit = true;
        }
        //decode the url in html name
        foreach ($json["fields"] as $field) {
            $field["htmlName"] = urldecode($field["htmlName"]);
        }

        return $this->render(
            'GrandsVoisinsBundle:Organisation:organisation.html.twig',
            array(
                'organisation'  => $json,
                'edit'          => $edit
            )
        );
    }

    public function saveOrganisationAction()
    {
        $edit = $_POST["edit"];
        unset($_POST["edit"]);

        foreach ($_POST as $key => $value) {
            unset($_POST[$key]);
            $_POST[str_replace("_", '.',urldecode($key))] = $value;
        }

        $info = $this->container
            ->get('semantic_forms.client')
            ->send($_POST);

        //TODO: a modifier pour prendre l'utilisateur courant !
        if ($info == 200) {
            if(!$edit){
                $organisationEntity = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('GrandsVoisinsBundle:Organisation');
                $query              = $organisationEntity->createQueryBuilder('q')
                    ->update()
                    ->set('q.sfOrganisation', ':link')
                    ->where('q.id=:id')
                    ->setParameter('link', $_POST["uri"])
                    ->setParameter('id', $this->getUser()->getfkOrganisation())
                    ->getQuery();
                $query->getResult();
            }

            $this->addFlash(
                'success',
                'tous est <b>ok !</b>'
            );

            return $this->redirectToRoute('profile');

        } else {
            $this->addFlash(
                'success',
                'quelque chose est <b>nok ...</b>'
            );
            return $this->redirectToRoute('organisation');
        }
    }

}
