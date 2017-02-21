<?php

namespace GrandsVoisinsBundle\Controller;

use GrandsVoisinsBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class AdminController extends Controller
{
    private $server = 'http://localhost:9000';
    private $baseLinkUserformAction = '/create-data?uri=&uri=http%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2FPerson';
    private $baseLinkSaveAction = '/save';
    private $baseLinkUserDisplayAction = '/form-data?displayuri='; //need the sf user link
    private $baseLinkLoginAction = '/authenticate';

    public function homeAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:home.html.twig',
          array(// ...
          )
        );
    }

    public function profileAction()
    {
        $json = file_get_contents(
          $this->server.$this->baseLinkUserDisplayAction.$this->getUser()->getSfLink()
        );
        $json = json_decode($json, true);

        return $this->render(
          'GrandsVoisinsBundle:Admin:profile.html.twig',
          array(
            "json" => $json,
          )
        );
    }

    public function organisationAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:organisation.html.twig',
          array(// ...
          )
        );
    }

    public function teamAction(Request $request)
    {
        // Find all users.
        // TODO Filter users : get only users attaged to this organisation.
        $userManager = $this->container->get('fos_user.user_manager');
        $users       = $userManager->findUsers();

        $form = $this->get('form.factory')->create(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get posted data of type user
            $data = $form->getData();

            // Generate password.
            $tokenGenerator = $this->container->get(
              'fos_user.util.token_generator'
            );
            $randomPassword = substr($tokenGenerator->generateToken(), 0, 12);
            $data->setPassword(
                password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
            );

            // Generate the token for the confirmation email
            $conf_token=$tokenGenerator->generateToken();
            $data->setConfirmationToken($conf_token);

            //Set the roles
            $data->addRole($form->get('access')->getData());

            // Save it.
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();

            //send the email for the new user
            $message = \Swift_Message::newInstance()
                ->setSubject('bonjour '.$data->getUsername() )
                ->setFrom('seb.mail.symfony@gmail.com')
                ->setTo($data->getEmail())
                ->setBody(
                    "Bonjour ".$data->getUsername()." !<br><br>
                    Pour valider votre compte utilisateur, merci de vous rendre sur http://localhost:8000/register/confirm/".$conf_token.".<br><br>
                    Ce lien ne peut être utilisé qu'une seule fois pour valider votre compte.<br><br>
                    Nom de compte : ".$data->getUsername()."<br>
                    Mot de passe : ".$randomPassword."<br><br>
                    Cordialement,
                    L'équipe"
                    ,
                    'text/html'
                );
            $this->get('mailer')->send($message);

            // TODO Grant permission to edit same organisation as current user.

            // Display message.
            $this->addFlash(
              'success',
              'Un compte à bien été créé pour <b>'.
              $data->getUsername().
              '</b>. Un email a été envoyé à <b>'.
              $data->getEmail().
              '</b> pour lui communiquer ses informations de connexion.'
            );

            // Go back to team page.
            return $this->redirectToRoute('team');
        }

        return $this->render(
          'GrandsVoisinsBundle:Admin:team.html.twig',
          array(
            'users' => $users,
            'formAddUser' => $form->createView(),
          )
        );
    }

    public function userDeleteAction($userId)
    {
        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $this->get('fos_user.user_manager');
        $user        = $userManager->findUserBy(['id' => $userId]);

        if (!$user) {
            // Display error message.
            $this->addFlash(
              'danger',
              'Utilisateur introuvable.'
            );
        } else {
            // Delete.
            $userManager->deleteUser($user);
            // Display success message.
            $this->addFlash(
              'success',
              'Le compte de <b>'.
              $user->getUsername().
              '</b> a bien été supprimé.'
            );
        }

        return $this->redirectToRoute('team');
    }

    public function userCreateSFProfileAction(){
        //get the form in JSON format
        $json = file_get_contents($this->server.$this->baseLinkUserformAction);
        //transform the JSON in array
        $data_json = json_decode($json,true);
        //decode the url in html name
        foreach ($data_json["fields"] as $field ){
            $field["htmlName"]=urldecode($field["htmlName"]);
        }

        return $this->render(
            'GrandsVoisinsBundle:Admin:profile_sf.html.twig',
            array(
                'form' => $data_json,
                'save_link' =>$this->server.$this->baseLinkSaveAction
            )
        );
    }

    public function userSaveSFProfileAction(){
        //open connection
        $ch = curl_init();
        //set POST variables
        $fields_string='';
        //url-ify the data for the POST
        foreach($_POST as $key=>$value) {$fields_string .= str_replace("_",'.',urldecode($key)).'='.$value.'&';}
        rtrim($fields_string, '&');
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->server.$this->baseLinkLoginAction);
        //curl_setopt($ch,CURLOPT_POST, true);
        //TODO : use the account of the user
        curl_setopt($ch,CURLOPT_POSTFIELDS, "userid=aa&password=aa");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__DIR__) . 'cookie/'.$this->getUser()->getUsername().'.txt');
        //execute post
        curl_exec($ch);
        curl_setopt($ch,CURLOPT_URL, $this->server.$this->baseLinkSaveAction);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__DIR__) .'cookie/'.$this->getUser()->getUsername().'.txt');
        curl_exec($ch);
        $info = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        //close connection
        curl_close($ch);
        if ($info ==200){
            $userEntity = $this->getDoctrine()->getManager()->getRepository('GrandsVoisinsBundle:User');
            $query= $userEntity->createQueryBuilder('q')
                ->update()
                ->set('q.sfLink',':link')
                ->where('q.id=:id')
                ->setParameter('link',$_POST["uri"])
                ->setParameter('id',$this->getUser()->getId())
                ->getQuery();
            $query->getResult();

            $this->addFlash(
                'success',
                'tous est <b>ok !</b>'
            );
            return $this->redirectToRoute('profile');
        }
        else{
            $this->addFlash(
                'success',
                'tous est <b>nok ...</b>'
            );
        } return $this->redirectToRoute('createSfProfile');
    }
}
