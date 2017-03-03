<?php

namespace GrandsVoisinsBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class AbstractController extends Controller
{
    public function render(
      $view,
      array $parameters = array(),
      Response $response = null
    ) {
        $user = $this->getUser();
        if ($user) {
            // Navigation is disabled when user has no profile.
            $parameters['menuVisible'] = !!$user->getSfLink();
        }

        return parent::render($view, $parameters, $response);
    }
}
