<?php

namespace semappsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebSiteController extends Controller
{
    public function indexAction()
    {
        return $this->render('semappsBundle:WebSite:index.html.twig');
    }
}
