<?php

namespace GrandsVoisinsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebSiteController extends Controller
{
    public function indexAction()
    {
        return $this->render('GrandsVoisinsBundle:WebSite:index.html.twig');
    }

}
