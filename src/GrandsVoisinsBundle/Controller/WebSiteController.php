<?php

namespace GrandsVoisinsBundle\Controller;

class WebSiteController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('GrandsVoisinsBundle:WebSite:index.html.twig');
    }
}
