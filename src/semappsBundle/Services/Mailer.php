<?php

/**
 * Created by PhpStorm.
 * User: tristan
 * Date: 24/02/17
 * Time: 15:33
 */
namespace semappsBundle\Services;
use semappsBundle\Entity\Organisation;
use semappsBundle\semappsConfig;
use Symfony\Component\Templating\EngineInterface;
use semappsBundle\Entity\User;
/**
 * E-mail Parameters
 */
class Mailer
{
    protected $mailer;
    protected $templating;
    private $encryption;
    private $from = "noreply@lesgrandsvoisins.org";
    CONST TYPE_USER = 1;
    CONST TYPE_RESPONSIBLE= 2;
    CONST TYPE_NOTIFICATION= 3;
    public function __construct($mailer, EngineInterface $templating,Encryption $encryption)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->encryption = $encryption;
    }

    protected function sendMessage($to, $subject, $body ,$from =null)
    {
        $mail = \Swift_Message::newInstance()
            ->setFrom(($from != null)?$from : $this->from)
            ->setTo($to)
            //->setBcc("sebastien.lemoine@cri-paris.org")
            ->setSubject($subject)
            ->setBody($body)

            ->setContentType('text/html');

        return $this->mailer->send($mail);
    }

    public function sendConfirmMessage($type,User $user,Organisation $organisation,  $url,$from =null)
    {
        //$subject = "Sortie de la carto de la mmmfest : Un outil pour nous connaître, partager et coopérer ! (On a besoin de toi !) "; //$user->getUsername()
        $content = $this->bodyMail( $user, $organisation, $url,$type);
        return $this->sendMessage($user->getEmail(), $content["subject"], $content["body"],$from);
    }

    public function sendNotification($type,User $user,Organisation $organisation,Array $to){
        $content = $this->bodyMail( $user, $organisation, null,$type);
        return $this->sendMessage($to, $content["subject"], $content["body"]);
    }

    // E-mail configuration
    private  function bodyMail(
      User $user,
      Organisation $organisation,
      $url,
      $type
    ) {
        $content = [];
        switch ($type){
            case self::TYPE_RESPONSIBLE :
                $content['subject'] = "Bienvenue sur la plateforme du MMM Fest !";
                $content['body'] = "Bonjour ".$user->getUsername()." ! <br><br>
                        Nous te souhaitons la bienvenue sur la plateforme SemApps !   http://sandbox.assemblee-virtuelle.org <br><br>
                        
                        Pour inscrire un atelier au festival, il te suffit de cliquer sur le lien ci-dessous : <br>".$url."<br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        
                        Voici tes identifiants :)<br>
                        Login : ".$user->getUsername()."<br>
                        Mot de passe : ".$this->encryption->decrypt($user->getSfUser())."<br>
                        Membre de l'organisation : ".$organisation->getName()."<br><br>
                        
                       L’interface d’administration te permettra alors de renseigner : <br>
												- Ton profil, <br>
												- Celui de ton organisation, <br>
												- Celui du projet faisant l’objet de l’atelier<br>
												- Le #CodeSocial (en cliquant sur document)<br>
												- Créer la fiche de l’atelier que vous organisez.<br><br>
                       
                       A très bientôt sur SemApps :-)
                       ";
                break;
            case self::TYPE_USER :
                $content['subject'] = "Bienvenue sur la plateforme du MMM Fest !";
                $content['body'] = "Bonjour ".$user->getUsername()." ! <br><br>
                        Nous te souhaitons la bienvenue sur la plateforme SemApps !   http://sandbox.assemblee-virtuelle.org <br><br>
                        
                        Pour t’inscrire au festival, il te suffit de cliquer sur le lien ci-dessous : <br>".$url."<br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        
                        Voici tes identifiants :)<br>
                        Login : ".$user->getUsername()."<br>
                        Mot de passe : ".$this->encryption->decrypt($user->getSfUser())."<br>
                        Membre de l'organisation : ".$organisation->getName()."<br><br>
                        
                       L’interface d’administration te permettra alors de renseigner ton profil et plein d’autres choses ;-)<br><br>
                       
                       A très bientôt au Millemont Makers & Music Festival :-)
                       ";
                break;
            default:
                $content['subject'] = "[NOTIF] Cartographie SemApps : Demande de création de compte !";
                $content['body'] = "Un nouvel utilisateur demande l'accès à l'application !</br></br>
                                  
                                    Email : ".$user->getEmail()."<br>
                                    Identifiant : ".$user->getUsername()."<br>
                                    Membre de l'organisation : ".$organisation->getName()."<br><br>
                                    
                                    Pour valider son compte, veuillez vous rendre dans l'onglet équipe et cliquer sur l'icone mail qui lui enverra ces infomration de connexion !<br><br>
                                   
                                   ";
                break;
        }


        return $content;
    }
}

