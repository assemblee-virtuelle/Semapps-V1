<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrganisationController extends Controller
{
    public function homeAction()
    {
        return $this->render('GrandsVoisinsBundle:Organisation:home.html.twig', array(
            // ...
        ));
    }

    public function newAction()
    {
        return $this->render('GrandsVoisinsBundle:Organisation:new.html.twig', array(
            // ...
        ));
    }

}
