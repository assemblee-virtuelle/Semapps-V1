<?php

/**
 * Created by PhpStorm.
 * User: tristan
 * Date: 24/02/17
 * Time: 15:33
 */
namespace GrandsVoisinsBundle\Services;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Component\Templating\EngineInterface;
use GrandsVoisinsBundle\Entity\User;
/**
 * E-mail Parameters
 */
class Mailer
{
    protected $mailer;
    protected $templating;
    //TODO:no-reply@lesgrandsvoising.org in parameters
    private $from = "seb.mail.symfony@gmail.com";

    public function __construct($mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function sendMessage($to, $subject, $body)
    {
        $mail = \Swift_Message::newInstance()
            ->setFrom($this->from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendConfirmMessage(User $user, $name, $url, $randomPassword, Organisation $organisation = null)
    {
        $subject = "Bonjour " . $user->getUsername();
        $to = $user->getEmail();
        $body = GrandsVoisinsConfig::bodyMail($name, $user, $url, $randomPassword, $organisation);
        $this->sendMessage($to, $subject, $body);
    }
}

