<?php

namespace GrandsVoisinsBundle\Controller;

class DefaultController extends AbstractController
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
