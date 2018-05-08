<?php

namespace semappsBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


abstract class AbstractComponentController extends Controller
{
    abstract function getGraph($id=null);
    abstract function getSfUser($id=null);
    abstract function getSfPassword($id=null);
    abstract function getSfLink($id=null);
    abstract function getElement($id=null);

    /**
     * @param $sfClient
     * @param $componentName
     * @param Request $request
     * @param null $id
     * @return \VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType
     * Génère le formulaire pour un $componentName
     */
    public function getSfForm($sfClient,$componentName,Request $request,$id =null)
    {
        $bundleName = $this->getBundleNameFromRequest($request);
        //common
        $componentConf = $this->getParameter($componentName.'Conf');

        //check if we impose the graph for the specific $componentName
        if(array_key_exists('graphuri',$componentConf) && $componentConf['graphuri'] != null)
            $graphURI = $componentConf['graphuri'];
        else
            $graphURI = $this->getGraph($id);

        // Build main form.
        //common idea but spec data
        $options = [
            'login'                 => $this->getSfUser($id),
            'password'              => $this->getSfPassword($id),
            'graphURI'              => $graphURI,
            'client'                => $sfClient,
            'sfConf'               	=> $componentConf,
            'spec'                  => $componentConf['spec'],
            'values'                => $this->getSfLink($id),

        ];

        // Same as FormType::class
        $componentForm = $bundleName.'\Form\\'.ucfirst(
                $componentName
            ).'Type';
        //common
        /** @var \VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType $form */
        $form = $this->createForm(
            $componentForm,
            $this->getElement($id),
            // Options.
            $options
        );
        return $form;
    }

    /**
     * @param $request
     * @return mixed
     * Récupère le nom du bundle dans la requête
     */
    protected function getBundleNameFromRequest($request){
        return explode("\\",$request->attributes->get('_controller'))[0];
    }
}
