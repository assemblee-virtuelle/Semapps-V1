<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ComponentController extends Controller
{
    public function showAction()
    {
        return $this->render('GrandsVoisinsBundle:Component:show.html.twig', array(
            // ...
        ));
    }

    public function showAllAction($type="")
    {
        return $this->render('GrandsVoisinsBundle:Component:show_all.html.twig', array(
            // ...
        ));
    }

    public function saveAction()
    {
        return $this->render('GrandsVoisinsBundle:Component:save.html.twig', array(
            // ...
        ));
    }

}
