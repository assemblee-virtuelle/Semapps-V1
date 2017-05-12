<?php

namespace GrandsVoisinsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */


class ConfirmRegistrationListener implements EventSubscriberInterface
{
    private $router;
    private $em;
    private $sfClient;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $security, EntityManager $em, SemanticFormsClient $sfClient)
    {
        $this->router = $router;
        $this->security =$security;
        $this->em = $em;
        $this->sfClient = $sfClient;
    }

    /**baseLinkRegisterAction
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
        );
    }

    public function onRegistrationConfirm(GetResponseUserEvent  $event)
    {
        $data = array( "userid" => $event->getUser()->getEmail(), "password" =>$event->getUser()->getSfUser(),"confirmPassword" =>$event->getUser()->getSfUser());
        $this->sfClient->post('/register',
            [
                'form_params' => $data
            ]);
        $url = $this->router->generate('fos_user_profile_show');
        $event->setResponse(new RedirectResponse($url));
    }
}
