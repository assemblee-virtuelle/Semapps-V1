<?php

namespace semappsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use semappsBundle\Services\Encryption;
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
    private $encryption;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $security, EntityManager $em, SemanticFormsClient $sfClient, Encryption $encryption)
    {
        $this->router = $router;
        $this->security =$security;
        $this->em = $em;
        $this->sfClient = $sfClient;
        $this->encryption = $encryption;
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
        $password = $this->encryption->decrypt($event->getUser()->getSfUser());
        $data = array( "userid" => $event->getUser()->getEmail(), "password" =>$password,"confirmPassword" =>$password);
        $this->sfClient->post('/register',
            [
                'form_params' => $data
            ]);

        $url = $this->router->generate('personComponentFormWithoutId',["uniqueComponentName" => "person"]);
        $event->setResponse(new RedirectResponse($url));
    }
}
