<?php

/**
 * Created by PhpStorm.
 * User: tristan
 * Date: 24/02/17
 * Time: 15:33
 */
namespace semappsBundle\Services;
use Symfony\Component\Templating\EngineInterface;
use semappsBundle\Entity\User;
/**
 * E-mail Parameters
 */
class Mailer
{
    protected $templating;
    protected $address;
    private $encryption;
    private $from;
    private $transport;
    CONST TYPE_USER = 1;
    CONST TYPE_RESPONSIBLE= 2;
    CONST TYPE_NOTIFICATION= 3;
    public function __construct($transport,$from,EngineInterface $templating,Encryption $encryption,$address)
    {
        $this->templating = $templating;
        $this->encryption = $encryption;
        $this->from = $from;
        $this->transport = $transport;
        $this->address = $address;
    }

    public function sendMessage($to, $subject, $body)
    {
        $mailer = \Swift_Mailer::newInstance($this->transport);
        $mail = \Swift_Message::newInstance()
            ->setFrom($this->from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');
        return $mailer->send($mail);
    }

    public function sendConfirmMessage($type, User $user, $url)
    {
        $content = $this->bodyMail( $user, $url,$type);
        return $this->sendMessage($user->getEmail(), $content["subject"], $content["body"]);
    }

    public function sendNotification($type, User $user, Array $to){
        $content = $this->bodyMail( $user,null,$type);
        return $this->sendMessage($to, $content["subject"], $content["body"]);
    }

    // E-mail configuration
    private  function bodyMail(
        User $user,
        $url,
        $type
    ) {
        $content = [];
        switch ($type){
            case self::TYPE_RESPONSIBLE :
                $content['subject'] = "Bienvenue sur la plateforme SemApps !";
                $content['body'] = "Bonjour ".$user->getUsername()." ! <br><br>
                        Bienvenue sur ".$this->address." <br><br>
                        Pour valider ton inscription, il te suffit de cliquer sur le lien ci-dessous : <br>".$url."<br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>";
                    $content['body'] .= "
                        Voici tes identifiants :)<br>
                        Login : ".$user->getUsername()."<br>
                        Mot de passe : ".$this->encryption->decrypt($user->getSfUser())."<br>
                        
                       L’interface d’administration te permettra alors de renseigner : <br>
												- Ton profil, <br>
												- Celui du projet faisant l’objet de l’atelier<br>
												- Le #CodeSocial (en cliquant sur document)<br>
												- Créer la fiche de l’atelier que vous organisez.<br><br>
                       
                       A très bientôt :-)
                       ";
                $content['body'] .= "A très bientôt sur SemApps :-)";
                break;
            case self::TYPE_USER :
                $content['subject'] = "Bienvenue sur la plateforme SemApps !";
                $content['body'] = "Bonjour ".$user->getUsername()." ! <br><br>
                        Nous te souhaitons la bienvenue sur la plateforme SemApps !   ".$this->address." <br><br>
                        
                        Pour t’inscrire au festival, il te suffit de cliquer sur le lien ci-dessous : <br>".$url."<br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        
                        Voici tes identifiants :)<br>
                        Login : ".$user->getUsername()."<br>
                        Mot de passe : ".$this->encryption->decrypt($user->getSfUser())."<br>
                        
                       L’interface d’administration te permettra alors de renseigner ton profil et plein d’autres choses ;-)<br><br>
                       
                       A très bientôt :-)
                       ";
                break;
            default:
                $content['subject'] = "[NOTIF] Cartographie SemApps : Demande de création de compte !";
                $content['body'] = "Un nouvel utilisateur demande l'accès à l'application !</br></br>
                                  
                                    Email : ".$user->getEmail()."<br>
                                    Identifiant : ".$user->getUsername()."<br>
                                    
                                    Pour valider son compte, veuillez vous rendre dans l'onglet équipe et cliquer sur l'icone mail qui lui enverra ces infomration de connexion !<br><br>
                                   
                                   ";
                break;
        }


        return $content;
    }
}

