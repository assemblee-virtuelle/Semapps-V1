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
    CONST TYPE_USER = 1;
    CONST TYPE_RESPONSIBLE= 2;
    CONST TYPE_NOTIFICATION= 3;
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
            //->setBcc("sebastien.lemoine@cri-paris.org")
            ->setSubject($subject)
            ->setBody($body)

            ->setContentType('text/html');

        return $this->mailer->send($mail);
    }

    public function sendConfirmMessage($type,User $user,Organisation $organisation,  $url,$from =null)
    {
        //$subject = "Sortie de la carto des Grands Voisins : Un outil pour nous connaître, partager et coopérer ! (On a besoin de toi !) "; //$user->getUsername()
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
                $content['subject'] = "Cartographie des Grands Voisins : Consultez et complétez votre fiche !";
                $content['body'] = "Salut à toi ".$user->getUsername()." !<br>
                                    Nous te souhaitons la bienvenue sur la carto des Grands Voisins ! http://reseau.lesgrandsvoisins.org/<br><br>
                                    
                                    Pour valider/accéder à votre profil, merci de vous rendre sur ".$url."<br>
                                    (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                                    Voici tes identifiants :):
                                    Login : ".$user->getUsername()."<br>
                                    Mot de passe : ".$user->getSfUser()."<br>
                                    Membre de l'organisation : ".$organisation->getName()."<br><br>
                                     
                                    En tant que référent de structure, nous t’invitons à :<br><br>
                                    
                                    Compléter la fiche de ta structure (Menu de gauche “Mon organisation”)<br>
                                    Inviter les membres de ton équipe / ton centre via l’onglet équipe dans l’interface d’administration. <br><br>
                                    
                                    La carto des Grand Voisins va vous permettre de communiquer en interne et vis à vis du grand public ! <br><br>
                                    
                                    Des questions ? Nous y répondons en live sur le channel carto du slack des Grands Voisins, par mail : contact@assemblee-virtuelle.org ou par tel : 06 28 34 54 99<br><br>
                                    Merci à vous,<br>
                                    William, Charlotte, Guillaume, Romain, Jean-Marc, Sébastien, Tristan, Frédéric et toute l’équipe ! (sans oublier Bobby !) <br><br>
                                    
                                    Cette application 100% open-source a été développée par l’équipe de l’Assemblée Virtuelle (que vous pouvez soutenir !), le projet a reçu 2000 euros de la part des Grands Voisins, les données sont hébergées par Aurore, elles ne sont pas revendues, ni utilisées en dehors de ce projet. L’application se base sur l’utilisation du web sémantique, et va évoluer dans les prochains mois (intégration de nouvelles fonctionnalités, capacité de réplication et de décentralisation de l’application. Objectif : créer un réseau social P2P de la transition).";
                break;
            case self::TYPE_USER :
                $content['subject'] = "Cartographie des Grands Voisins : Consultez et complétez votre fiche !";
                $content['body'] = "Salut à toi ".$user->getUsername()." ! <br><br>
                        Nous te souhaitons la bienvenue sur la carto des Grands Voisins !  http://reseau.lesgrandsvoisins.org/ <br><br>
                        
                        Pour valider/accéder à votre profil, merci de vous rendre sur ".$url."<br><br>
                        (Ce lien ne peut être utilisé qu'une seule fois, il sert à valider votre compte.)<br><br>
                        Identifiant : ".$user->getUsername()."<br>
                        Mot de passe : ".$user->getSfUser()."<br>
                        Membre de l'organisation : ".$organisation->getName()."<br><br>
                        
                        Quelques remarques à ce sujet :<br>
                        - Tu ne peux te connecter qu’aux personnes disposant d’un profil sur la carto, si tu ne les trouves pas, invite les ;-)<br>
                        - Les centres d’intérêts, les compétences, les offres et besoins de ressources sont gérés avec Wikipedia, pour l’instant on ne peut les saisir qu’en anglais, ce qui devrait évoluer dans les prochains mois. Utilise un traducteur si jamais ! <br>
                        - Si vous souhaitez devenir admin de votre orga, demandez aux personnes identifiées comme responsables ou admin de votre orga sur la carto ;-) Il leur suffira de changer votre rôle dans “Equipe”<br><br>
                       
                        Si tu es référent / administrateur de ton organisation, n’hésite pas à :<br>
                        - Compléter la fiche de ta structure (Menu de gauche “Mon organisation”)<br>
                        - Inviter les membres de ton équipe / ton centre via l’onglet équipe dans l’interface d’administration.<br>
                        - En renseignant des projets que vous développez, des événements que vous organisez, des propositions que vous portez (dans le menu admin de gauche ...) <br><br>
                        
                        Des questions ? Nous y répondons en live sur le channel carto du slack des Grands Voisins, par mail : contact@assemblee-virtuelle.org ou par tel : 06 28 34 54 99<br><br>
                        Merci à vous,<br><br>
                        
                        William, Charlotte, Guillaume, Romain, Jean-Marc, Sébastien, Tristan, Frédéric et toute l’équipe ! (sans oublier Bobby !) <br><br>
                        
                        Cette application 100% open-source a été développée par l’équipe de l’Assemblée Virtuelle (que vous pouvez soutenir !), le projet a reçu 2000 euros de la part des Grands Voisins, les données sont hébergées par Aurore, elles ne sont pas revendues, ni utilisées en dehors de ce projet. L’application se base sur l’utilisation du web sémantique, et va évoluer dans les prochains mois (intégration de nouvelles fonctionnalités, capacité de réplication et de décentralisation de l’application. Objectif : créer un réseau social P2P de la transition). ";

                break;
            default:
                $content['subject'] = "[NOTIF] Cartographie des Grands Voisins : Demande de création de compte !";
                $content['body'] = "Un nouvel utilisateur demande l'accès à l'application !</br></br>
                                  
                                    Email : ".$user->getEmail()."<br>
                                    Identifiant : ".$user->getUsername()."<br>
                                    Membre de l'organisation : ".$organisation->getName()."<br><br>
                                    
                                    Pour valider son compte, veuillez vous rendre dans l'onglet équipe et cliquer sur l'icone mail qui lui enverra ces infomration de connexion !<br><br>
                                    
                                    Des questions ? Nous y répondons en live sur le channel carto du slack des Grands Voisins, par mail : contact@assemblee-virtuelle.org ou par tel : 06 28 34 54 99<br><br>
                                    Merci à vous,<br><br>
                                    William, Charlotte, Guillaume, Romain, Jean-Marc, Sébastien, Tristan, Frédéric et toute l’équipe ! (sans oublier Bobby !) <br><br>
                                   ";
                break;
        }


        return $content;
    }
}

