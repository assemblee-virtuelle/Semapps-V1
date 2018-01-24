<?php

namespace semappsBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMultipleComponentController extends AbstractComponentController
{
    var $sfLink;
    public abstract function componentList($componentConf,$componentType,$graphURI);
    public abstract function removeComponent($uri);
    public function listAction($componentName,Request $request)
    {
        $bundleName = $this->getBundleNameFromRequest($request);
        $componentList = $this->getParameter('semantic_forms.component');
        $componentConf = $this->getParameter($componentName.'Conf');

        if(array_key_exists('graphuri',$componentConf) && $componentConf['graphuri'] != null)
            $graphURI = $componentConf['graphuri'];
        else
            $graphURI = $this->getGraph(null);

        $listContent = $this->componentList($componentConf,$componentList[$componentName],$graphURI);
        return $this->render(
            $bundleName.':'.ucfirst($componentName).':'.$componentName.'List.html.twig',
            array(
                'componentName' => $componentName,
                'plural'        => $componentName.'(s)',
                'listContent'   => $listContent,
            )
        );
    }

    public function removeAction($componentName,Request $request){

        self::removeComponent($request->get('uri'));
        return $this->redirectToRoute(
            'componentList', ["componentName" => $componentName]
        );

    }

    function getSfLink($id = null)
    {
        return $this->sfLink;
    }
    public function setSfLink($sfLink){
        $this->sfLink = $sfLink;
    }
    function getElement($id = null)
    {
        return null;
    }
}
