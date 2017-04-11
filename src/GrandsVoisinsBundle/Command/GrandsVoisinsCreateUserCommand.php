<?php

namespace GrandsVoisinsBundle\Command;

use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GrandsVoisinsCreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('GrandsVoisins:create:user')
            ->setDescription('Adding a user on one organization')
            ->addArgument(
                'orgaId',
                InputArgument::REQUIRED,
                'id of the organization'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'username of the responsible of the organization'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'email of the responsible of the organization'
            )
            ->addOption('admin', null, InputOption::VALUE_REQUIRED, false);
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

        $questions = array();
        if (!$input->getArgument('orgaId')) {
            $question = new Question(
                'Please choose the id of the organization:'
            );
            $question->setValidator(
                function ($orgaId) {
                    $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
                    $organizationRepository = $em->getRepository('GrandsVoisinsBundle:Organisation');
                    if (empty($orgaId)) {
                        throw new \Exception('organization can not be empty');
                    }
                    elseif (empty($organizationRepository->find($orgaId))) {
                        throw new \Exception('organization not found');
                    }
                    return $orgaId;
                }
            );
            $questions['orgaId'] = $question;
        }

        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(
                function ($username) {
                    if (empty($username)) {
                        throw new \Exception('organization can not be empty');
                    }

                    return $username;
                }
            );
            $questions['username'] = $question;
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose a email:');
            $question->setValidator(
                function ($email) {
                    if (empty($email)) {
                        throw new \Exception('organization can not be empty');
                    }

                    return $email;
                }
            );
            $questions['email'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask(
                $input,
                $output,
                $question
            );
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
        $token  = $this->getContainer()->get('fos_user.util.token_generator');
        $mailer = $this->getContainer()->get(
            'GrandsVoisinsBundle.EventListener.SendMail'
        );

        $user         = new User();
        $organization = new Organisation();

        $orgaId           = $input->getArgument('orgaId');
        $username         = $input->getArgument('username');
        $email            = $input->getArgument('email');

        $role[] = $input->getOption('admin') != false ?
            "ROLE_ADMIN" : "ROLE_MEMBER";

        $output->writeln(
            sprintf(
                "creating the user %s with argument: \n\t-username:%s\n\t-email:%s\n\t-role:%s\n\t-organization id:%s",
                $username,
                $username,
                $email,
                implode(",", $role),
                $organization->getId()
            )
        ); // <-- finish
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles($role);
        $user->setFkOrganisation($orgaId);
        // Generate password.
        $randomPassword = substr($token->generateToken(), 0, 12);
        $user->setPassword(
            password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
        );
        $user->setSfUser($randomPassword);

        // Generate the token for the confirmation email
        $conf_token = $token->generateToken();
        $user->setConfirmationToken($conf_token);

        $em->persist($user);
        $em->flush($user);
        $output->writeln(sprintf("user %s created !", $username));

        $output->writeln(
            sprintf(
                "sending the email for the user with:\n\t-username:%s\n\t-password:%s ",
                $username,
                $randomPassword
            )
        );

        $url = $this->getContainer()->get('router')->generate(
            'fos_user_registration_confirm',
            array('token' => $conf_token),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $output->writeln($url);
        $url = str_replace('localhost',$this->getContainer()->getParameter('gv.domain'),$url);
        $output->writeln($url);
        $mailer->sendConfirmMessage(
            $user,
            GrandsVoisinsConfig::ORGANISATION,
            $url,
            $randomPassword,
            $organization
        );
        $output->writeln("Email send ! ");
        $output->writeln('Everything is ok !');
    }

}
