<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 20/02/2017
 * Time: 13:42
 */

namespace GrandsVoisinsBundle\EventListener;

use GrandsVoisinsBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class sfProfileCreatedListener
{
    private $router;
    private $security;
    private $path;

    public function __construct(
      UrlGeneratorInterface $router,
      TokenStorageInterface $security,
      $path
    ) {
        $this->router = $router;
        $this->security = $security;
        $this->path = $path;
    }

    //TODO see if we can do better
    public function onKernelRequest(GetResponseEvent $event)
    {
        //route qu'il esquive
        if (strpos(
            $event->getRequest()->getRequestUri(),
            "/_wdt"
          ) === false && strpos(
            $event->getRequest()->getRequestUri(),
            "/admin/saveSfProfile"
          ) === false
        ) {
            //je regarde si l'utilisateur est connecté
            if ($this->security->getToken() != null) {
                if ($this->security->getToken()->getUsername() != "anon.") {
                    //je regarde s'il n'a pas déjà un profile semantic form
                    if ($this->security->getToken()->getUser()->getSfLink(
                      ) == ""
                    ) {
                        //je regarde s'il va autre part que la route envoyé au listener ( doit être celle de sfProfile )
                        if (strpos(
                            $event->getRequest()->getRequestUri(),
                            $this->path
                          ) === false
                        ) {
                            $event->setResponse(
                              new RedirectResponse(
                                $this->router->generate('sfProfile')
                              )
                            );
                        }
                    }
                }
            }
        }
    }

}
