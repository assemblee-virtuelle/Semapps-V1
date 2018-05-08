<?php

namespace semappsBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMultipleComponentController extends AbstractComponentController
{
    var $sfLink;
    public abstract function componentList($componentConf,$graphURI);
    public abstract function removeComponent($uri);

    /**
     * @param $componentName
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * liste tous les $componentName
     */
    public function listAction($componentName,Request $request)
    {
        $bundleName = $this->getBundleNameFromRequest($request);
        //common
        $componentConf = $this->getParameter($componentName.'Conf');

        //check if we impose the graph for the specific $componentName
        if(array_key_exists('graphuri',$componentConf) && $componentConf['graphuri'] != null)
            $graphURI = $componentConf['graphuri'];
        else
            $graphURI = $this->getGraph(null);

        //get the list of component
        $listContent = $this->componentList($componentConf,$graphURI);

        //display
        return $this->render(
            $bundleName.':'.ucfirst($componentName).':'.$componentName.'List.html.twig',
            array(
                'componentName' => $componentName,
                'plural'        => $componentName.'(s)',
                'listContent'   => $listContent,
            )
        );
    }

    /**
     * @param $componentName
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Supprime un $componentName
     */
    public function removeAction($componentName,Request $request){
        //get component uri form request
        $uri = $request->get('uri');

        //remove this component from request
        $this->removeComponent($uri);

        //redirect to the list of componentName
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
