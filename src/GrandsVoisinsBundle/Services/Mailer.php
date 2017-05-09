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
    private $from = "noreply@lesgrandsvoisins.org";

    public function __construct($mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function sendMessage($to, $subject, $body ,$from =null)
    {
        $mail = \Swift_Message::newInstance()
            ->setFrom(($from != null)?$from : $this->from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendConfirmMessage(User $user, $url, $randomPassword,$from =null)
    {
        $subject = "Sortie de la carto des Grands Voisins : Un outil pour nous connaître, partager et coopérer ! (On a besoin de toi !) "; //$user->getUsername()
        $to = $user->getEmail();
        $body = GrandsVoisinsConfig::bodyMail( $user, $url, $randomPassword);
        $this->sendMessage($to, $subject, $body,$from);
    }
}

