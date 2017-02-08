<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GrandsVoisinsBundle:Default:index.html.twig');
    }
    public function searchAction()
    {
        return $this->render('GrandsVoisinsBundle:Default:index.html.twig');
    }
}
