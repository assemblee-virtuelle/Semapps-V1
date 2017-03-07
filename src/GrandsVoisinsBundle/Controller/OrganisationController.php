<?php

namespace GrandsVoisinsBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\Form\AdminSettings;
use GrandsVoisinsBundle\Form\OrganisationType;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class OrganisationController extends AbstractController
{
    public function allAction(Request $request)
    {

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );
        $organisations = $organisationEntity->findAll();

        //form pour l'organisation
        $organisation = new Organisation();
        $form = $this->get('form.factory')->create(
            OrganisationType::class,
            $organisation
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //for the organisation
            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);

            // actually executes the queries (i.e. the INSERT query)
            try {
                $em->flush($organisation);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', "le nom de l'orgnanisation que vous avez saisi est déjà présent");
                return $this->redirectToRoute('all_orga');
            }
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
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                //removing the organization added before
                $em = $this->getDoctrine()->resetManager();
                $em->remove($em->getRepository('GrandsVoisinsBundle:Organisation')->find($organisation->getId()));
                $em->flush();
                $this->addFlash('danger', "l'utilisateur saisi est déjà présent");

                return $this->redirectToRoute('all_orga');
            }

            $organisation->setFkResponsable($user->getId());
            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                //removing the organization and the user added before
                $em = $this->getDoctrine()->resetManager();
                $em->remove($em->getRepository('GrandsVoisinsBundle:User')->find($user->getId()));
                $em->remove($em->getRepository('GrandsVoisinsBundle:Organisation')->find($organisation->getId()));
                $em->flush();
                $this->addFlash('danger', "Problème lors de la mise à jour des champs, veuillez contacter un administrateur");

                return $this->redirectToRoute('all_orga');
            }
            // send email to the new organization
            $this->get('GrandsVoisinsBundle.EventListener.SendMail')
                ->sendConfirmMessage($user, GrandsVoisinsConfig::ORGANISATION, $conf_token, $randomPassword, $organisation);

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

        return $this->render(
            'GrandsVoisinsBundle:Organisation:home.html.twig',
            array(
                "organisations" => $organisations,
                "formAddOrganisation" => $form->createView(),
            )
        );
    }

    public function newOrganisationAction()
    {
        $sfClient = $this->container->get('semantic_forms.client');

        /* @var $organisation \GrandsVoisinsBundle\Repository\OrganisationRepository */
        // questionner la base pour savoir si l'orga est deja créer
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:Organisation'
        );

        $userEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:User'
        );

        /* @var $organisation \GrandsVoisinsBundle\Entity\Organisation */
        $organisation = $organisationEntity->findOneById(
            $this->GetUser()->getFkOrganisation()
        );

        $responsible = $userEntity->find($organisation->getFkResponsable());

        if (is_null($organisation->getSfOrganisation())) {
            $json = $sfClient->createFoaf('Organization');
            $edit = false;
        } else {
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
                'organisation' => $json,
                'edit' => $edit,
                'graphURI' => $responsible->getGraphURI()
            )
        );
    }

    public function saveOrganisationAction()
    {
        $edit = $_POST["edit"];
        unset($_POST["edit"]);

        $userEntity = $this->getDoctrine()->getManager()->getRepository(
            'GrandsVoisinsBundle:User'
        );

        $responsable = $userEntity->findOneBy(["email" => explode(':', urldecode($_POST["graphURI"]))[1]]);


        $info = $this->container
            ->get('semantic_forms.client')
            ->send($_POST, $responsable->getEmail(), $responsable->getSfUser());

        //TODO: a modifier pour prendre l'utilisateur courant !
        if ($info == 200) {
            if (!$edit) {
                $organisationEntity = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('GrandsVoisinsBundle:Organisation');
                $query = $organisationEntity->createQueryBuilder(
                    'q'
                )
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

    public function settingsAction(Request $request)
    {
        $user = $this->GetUser();

        $form = $this->get('form.factory')->create(AdminSettings::class, $user);
        $picture = $this->createFormBuilder($user)
            ->add('pictureName',FileType::class,array('data_class' =>null))
            ->add('oldPicture',HiddenType::class,array('mapped' => false,'data'=>$user->getPictureName()))
            ->add('enregister',SubmitType::class)
            ->getForm();

        $picture->handleRequest($request);

        if ($picture->isSubmitted() && $picture->isValid()) {
            if($picture->get('oldPicture')->getData()){
                $this->get('GrandsVoisinsBundle.fileUploader')->remove($picture->get('oldPicture')->getData());
            }
            $user->setPictureName($this->get('GrandsVoisinsBundle.fileUploader')->upload($user->getPictureName()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('settings');
        }

        return $this->render(
            'GrandsVoisinsBundle:Admin:settings.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
                'picture' => $picture->createView()
            )
        );
    }

}
