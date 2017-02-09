<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    public function showAction()
    {
        return $this->render('GrandsVoisinsBundle:Admin:show.html.twig', array(
            // ...
        ));
    }

    public function testAction()
    {
        return $this->render('GrandsVoisinsBundle:Admin:test.html.twig', array(
            // ...
        ));
    }

}
