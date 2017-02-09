<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
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
        return $this->render(
          'GrandsVoisinsBundle:Admin:profile.html.twig',
          array(// ...
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

    public function inviteAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:invite.html.twig',
          array(// ...
          )
        );
    }

    public function testAction()
    {
        return $this->render(
          'GrandsVoisinsBundle:Admin:test.html.twig',
          array(// ...
          )
        );
    }

}
