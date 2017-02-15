<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\HttpFoundation\Request;
use GrandsVoisinsBundle\Entity\User;

class AdminController extends Controller
{
    public function homeAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:home.html.twig',
          array(// ...
          )
        );
    }

    public function profileAction()
    {
        $link = "http%3A%2F%2Fjmvanel.free.fr%2Fjmv.rdf%23me";
        $json = file_get_contents("http://163.172.179.125:9112/form-data?displayuri=".$link);
        $json = json_decode($json,true);

        return $this->render(
          'GrandsVoisinsBundle:Admin:profile.html.twig',
          array("json" =>$json
          )
        );
    }

    public function organisationAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:organisation.html.twig',
          array(// ...
          )
        );
    }

    public function teamAction(Request $request)
    {
        // Find all users.
        // TODO Filter users : get only users attaged to this organisation.
        $userManager = $this->container->get('fos_user.user_manager');
        $users       = $userManager->findUsers();

        $accessLevels = array(
          'Administrateur' => 'admin',
          'Editeur'        => 'editor',
          'Membre'         => 'member',
        );

        // Create user form.
        $form = $this->createFormBuilder()
          ->add(
            'login',
            TextType::class,
            array(
              'constraints' => array(
                new NotBlank(),
              ),
            )
          )
          ->add(
            'email',
            TextType::class,
            array(
              'constraints' => array(
                new Email(),
              ),
            )
          )
          ->add(
            'access',
            ChoiceType::class,
            [
              'choices' => $accessLevels,
            ]
          )
          ->add('submit', SubmitType::class, array())
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get posted data.
            $data = $form->getData();

            // Create user.
            $user = new User();

            // Save login.
            $user->setUsername($data['login']);
            $user->setEmail($data['email']);

            // Generate password.
            $tokenGenerator = $this->container->get(
              'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $user->setPassword($randomPassword);

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // TODO Grant permission to edit same organisation as current user.

            // Display message.
            $this->addFlash(
              'success',
              'Un compte à bien été créé pour <b>'.
              $data['login'].
              '</b>. Un email a été envoyé à <b>'.
              $data['email'].
              '</b> pour lui communiquer ses informations de connexion.'
            );

            // Go back to team page.
            return $this->redirectToRoute('team');
        }

        return $this->render(
          'GrandsVoisinsBundle:Admin:team.html.twig',
          array(
            'users'        => $users,
            'accessLevels' => $accessLevels,
            'formAddUser'  => $form->createView(),
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
}
