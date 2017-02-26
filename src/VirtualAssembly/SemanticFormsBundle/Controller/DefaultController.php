<?php

namespace VirtualAssembly\SemanticFormsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SemanticFormsBundle:Default:index.html.twig');
    }
}
