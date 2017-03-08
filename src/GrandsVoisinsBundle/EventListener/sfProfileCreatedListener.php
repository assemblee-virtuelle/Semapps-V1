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
    private $route;

    public function __construct(
      UrlGeneratorInterface $router,
      TokenStorageInterface $security,
      $route
    ) {
        $this->router   = $router;
        $this->security = $security;
        $this->route    = $route;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $routeCurrent = $event->getRequest()->get('_route');

        // We are not on a system route or on the profile route.
        if ($routeCurrent &&
          $routeCurrent{0} !== '_' &&
          $routeCurrent !== $this->route &&
          $routeCurrent !== $this->route.'Save'
        ) {
            $token = $this->security->getToken();
            // Check user is logged.
            if ($token &&
              $token->getUsername() !== 'anon.' &&
              !$token->getUser()->getSfLink()
            ) {
                $event->setResponse(
                  new RedirectResponse(
                    $this->router->generate(
                      $this->route
                    )
                  )
                );
            }
        }
    }
}
