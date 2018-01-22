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

class AdministrationOrganisationController extends Controller
{

    public function allAction(Request $request)
    {
        /** @var \semappsBundle\Services\Encryption $encryption */
        $encryption = $this->container->get('semappsBundle.encryption');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'semappsBundle:Organization'
        );
        $organisations      = $organisationEntity->findAll();

        //form pour l'organisation
        $organisation = new Organization();
        $form         = $this->get('form.factory')->create(
            OrganisationMemberType::class,
            $organisation
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //for the organisation
            $em = $this->getDoctrine()->getManager();

            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);
            try {
                $em->flush($organisation);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'danger',
                    "le nom de l'orgnanisation que vous avez saisi est déjà présent"
                );

                return $this->redirectToRoute('orgaList');
            }
            $organisation->setGraphURI(
                semappsConfig::PREFIX.$organisation->getId().'/'.$organisation->getName().'-org'
            );
            $em->flush();

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

            $user->setSfUser($encryption->encrypt($randomPassword));

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
                $em->remove(
                    $em->getRepository('semappsBundle:Organization')->find(
                        $organisation->getId()
                    )
                );
                $em->flush();
                $this->addFlash(
                    'danger',
                    "l'utilisateur saisi est déjà présent"
                );

                return $this->redirectToRoute('orgaList');
            }

            $organisation->setFkResponsable($user->getId());
            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($organisation);
            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                //removing the organization and the user added before
                $em = $this->getDoctrine()->resetManager();
                $em->remove(
                    $em->getRepository('semappsBundle:User')->find(
                        $user->getId()
                    )
                );
                $em->remove(
                    $em->getRepository('semappsBundle:Organization')->find(
                        $organisation->getId()
                    )
                );
                $em->flush();
                $this->addFlash(
                    'danger',
                    "Problème lors de la mise à jour des champs, veuillez contacter un administrateur"
                );

                return $this->redirectToRoute('orgaList');
            }
            $sendEmail = $form->get('sendEmail')->getData();
            if($sendEmail){
                $url = $this->generateUrl(
                    'fos_user_registration_confirm',
                    array('token' => $conf_token),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                // send email to the new organization
                $mailer = $this->get('semappsBundle.EventListener.SendMail');
                $result = $mailer->sendConfirmMessage(
                    $mailer::TYPE_RESPONSIBLE,
                    $user,
                    $organisation,
                    $url
                );

                // TODO Grant permission to edit same organisation as current user.
                // Display message.
                if($result){
                    $this->addFlash(
                        'success',
                        'Un compte à bien été créé pour <b>'.
                        $user->getUsername().
                        '</b>. Un email a été envoyé à <b>'.
                        $user->getEmail().
                        '</b> pour lui communiquer ses informations de connexion.'
                    );
                }else{
                    $this->addFlash(
                        'danger',
                        'Un compte à bien été créé pour <b>'.
                        $user->getUsername().
                        "</b>. mais l'email n'est pas parti à l'adresse <b>".
                        $user->getEmail().
                        '</b>'
                    );
                }
            }
            else{
                $this->addFlash(
                    'success',
                    'Un compte à bien été créé pour <b>'.
                    $user->getUsername()
                );
            }

            return $this->redirectToRoute('orgaList');
        }

        return $this->render(
            'semappsBundle:Organization:home.html.twig',
            array(
                "organisations"       => $organisations,
                "formAddOrganisation" => $form->createView(),
            )
        );
    }

    public function orgaExportAction()
    {
        $lines              = [];
        $sfClient           = $this->container->get('semantic_forms.client');
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'semappsBundle:Organization'
        );
        $organisations      = $organisationEntity->findAll();
        $columns            = [];

        foreach ($organisations as $organisation) {
            // Sparql request.
            $properties = $sfClient->uriProperties(
                $organisation->getSfOrganisation()
            );
            // We have key / pair values.
            $lines[] = $properties;
            // Save new columns if some are missing.
            $columns = array_unique(
                array_merge($columns, array_keys($properties))
            );
        }

        $output = [];
        // Rebuild array based on strict columns list.
        foreach ($lines as $incompleteLine) {
            $line = [];
            foreach ($columns as $key) {
                $line[$key] = isset($incompleteLine[$key]) ? is_array($incompleteLine[$key])? implode(',',$incompleteLine[$key]) : $incompleteLine[$key] : '';
            }
            $output[] = $line;
        }

        // Append first lint.
        array_unshift($output, $columns);
        $excel = new SimpleExcel('csv');
        /** @var \SimpleExcel\Writer\CSVWriter $writer */
        $writer = $excel->writer;
        // Fill.
        $writer->setData(
            $output
        );
        $writer->setDelimiter(";");
        $writer->saveFile('SemApps-'.date('Y_m_d'));

        return $this->redirectToRoute('orgaList');
    }

    public function orgaDeleteAction($orgaId)
    {
        $organisationRepository = $this->getDoctrine()
            ->getManager()
            ->getRepository('semappsBundle:Organization');

        $organisation  = $organisationRepository->find($orgaId);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$organisation) {
            // Display error message.
            $this->addFlash(
                'danger',
                'Organization introuvable.'
            );
        } else {
            // Delete.
            $entityManager->remove($organisation);

            $entityManager
                ->getConnection()
                ->prepare(
                    'DELETE FROM user WHERE fk_organisation = :id_organisation'
                )
                ->execute([':id_organisation' => $organisation->getId()]);

            $entityManager->flush();
            // Display success message.
            $this->addFlash(
                'success',
                'L\'organisation <b>'.
                $organisation->getName().
                '</b> a bien été supprimée.'
            );
        }

        return $this->redirectToRoute('orgaList');
    }
}
