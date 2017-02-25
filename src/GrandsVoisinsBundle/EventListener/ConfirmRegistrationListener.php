<?php

namespace GrandsVoisinsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */


class ConfirmRegistrationListener implements EventSubscriberInterface
{
    private $router;
    private $server = 'localhost:9000';
    private $baseLinkRegisterAction = '/register';
    private $em;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $security, EntityManager $em)
    {
        $this->router = $router;
        $this->security =$security;
        $this->em = $em;
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

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $this->server.$this->baseLinkRegisterAction);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, "userid=".$event->getUser()->getEmail().'&'.
            "password=".$event->getUser()->getSfUser().'&'.
            "confirmPassword=".$event->getUser()->getSfUSer());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);


        $userEntity = $this->em->getRepository('GrandsVoisinsBundle:User');
        $query= $userEntity->createQueryBuilder('q')
            ->update()
            ->set('q.sfUser',':link')
            ->where('q.id=:id')
            ->setParameter('link',urlencode($event->getUser()->getEmail()))
            ->setParameter('id',$event->getUser()->getId())
            ->getQuery();
        $query->getResult();


        $url = $this->router->generate('profile');
        $event->setResponse(new RedirectResponse($url));
    }
}