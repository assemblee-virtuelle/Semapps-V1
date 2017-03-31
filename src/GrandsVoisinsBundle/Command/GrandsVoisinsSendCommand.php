<?php

namespace GrandsVoisinsBundle\Command;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GrandsVoisinsSendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('GrandsVoisins:send')
            ->setDescription(
                'Send the confirmation email for the user selected'
            )
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Id of the user'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument('id')) {
            $question = new Question('Please choose a id:');
            $question->setValidator(
                function ($id) {
                    $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
                    $user   = $em->getRepository('GrandsVoisinsBundle:User')->find($id);
                    if (empty($id)) {
                        throw new \Exception('organization can not be empty');
                    }
                    elseif(empty($user)){
                            throw new \Exception('ID incorrect, no person correspond to this id');
                    }
                    elseif($user->isEnabled()){
                        throw new \Exception('ID incorrect, id already activated');
                    }
                    elseif(empty($user->getConfirmationToken())){
                        throw new \Exception('ID incorrect, error with the conf token');
                    }
                    return $id;
                }
            );
            $questions['id'] = $question;
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
        $id     = $input->getArgument('id');
        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get(
            'GrandsVoisinsBundle.EventListener.SendMail'
        );

        $userRepository         = $em->getRepository('GrandsVoisinsBundle:User');
        $organisationRepository = $em->getRepository('GrandsVoisinsBundle:Organisation');

        $user = $userRepository->find($id);

        $organization = $organisationRepository->find($user->getFkOrganisation());

        $url = $this->getContainer()->get('router')->generate(
            'fos_user_registration_confirm',
            array('token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $url = str_replace('localhost',$this->getContainer()->getParameter('gv.domain'),$url);
        $mailer->sendConfirmMessage(
            $user,
            GrandsVoisinsConfig::ORGANISATION,
            $url,
            $user->getSfUser(),
            $organization
        );
        $output->writeln("Email send ! ");
        $output->writeln('Everything is ok !');
    }

}
