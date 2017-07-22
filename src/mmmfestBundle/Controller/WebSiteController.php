<?php

namespace mmmfestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebSiteController extends Controller
{
    public function indexAction()
    {
        return $this->render('mmmfestBundle:WebSite:index.html.twig');
    }
}
