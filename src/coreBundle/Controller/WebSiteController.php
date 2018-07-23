<?php

namespace coreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebSiteController extends Controller
{
    public function indexAction()
    {
        return $this->render('coreBundle:WebSite:index.html.twig');
    }
}
