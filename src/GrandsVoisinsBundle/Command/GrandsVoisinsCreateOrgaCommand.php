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

class GrandsVoisinsCreateOrgaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
          ->setName('GrandsVoisins:create:orga')
          ->setDescription(
            'Create a new organization and a responsible of this organization'
          )
          ->addArgument(
            'organization',
            InputArgument::REQUIRED,
            'name of the organization'
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
          ->addOption('super_admin', null, InputOption::VALUE_REQUIRED, false);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();
        if (!$input->getArgument('organization')) {
            $question = new Question(
              'Please choose a name for the organization:'
            );
            $question->setValidator(
              function ($organization) {
                  if (empty($organization)) {
                      throw new \Exception('organization can not be empty');
                  }

                  return $organization;
              }
            );
            $questions['organization'] = $question;
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

        $organizationName = $input->getArgument('organization');
        $username         = $input->getArgument('username');
        $email            = $input->getArgument('email');

        $role[] = $input->getOption('super_admin') != false ?
          "ROLE_SUPER_ADMIN" : "ROLE_ADMIN";

        $output->writeln(
          sprintf(
            "creating the organization %s with argumment: \n\t-name:%s",
            $organizationName,$organizationName
          )
        );
        $organization->setName($organizationName);

        $em->persist($organization);
        $em->flush($organization);
        $organization->setGraphURI(GrandsVoisinsConfig::PREFIX.$organization->getId().'-org');
        $em->persist($organization);
        $em->flush($organization);

        $output->writeln(
          sprintf("organization %s created !", $organizationName)
        );

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
        $user->setFkOrganisation($organization->getId());
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
            "updating the organization %s to place the user %s as responsible",
            $organizationName,
            $username
          )
        );
        $organization->setFkResponsable($user->getId());
        $em->persist($organization);
        $em->flush($organization);
        $output->writeln(
          sprintf("organization %s updated !", $organizationName)
        );

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
        $result = $mailer->sendConfirmMessage(
          $user,
          $url,
          $randomPassword
        );
        if($result){
            $output->writeln("Email send ! ");
            $output->writeln('Everything is ok !');
        }else{
            $output->writeln("Email not send !!! ");
            $output->writeln('Everything is not ok !!!!');
        }
    }

}
