<?php

namespace semappsBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractUniqueComponentController extends AbstractComponentController
{
		abstract public function getGraph($id);
		abstract public function getUniqueElement($id);
		abstract public function getUriLinkUniqueElement($id);
		abstract public function getSfUser($id);
		abstract public function getSfPassword($id);
		abstract public function specificTreatment($sfClient,$form,$request,$componentName,$id);


		public function addAction($uniqueComponentName,$id =null,Request $request)
    {
    		$bundleName = $this->getBundleNameFromRequest($request);
				//common
				$sfClient       = $this->container->get('semantic_forms.client');
				//common
				$uniqueComponentConf = $this->getParameter($uniqueComponentName.'Conf');

				// Build main form.
				//common idea but spec data
				$options = [
					'login'                 => $this->getSfUser($id),
					'password'              => $this->getSfPassword($id),
					'graphURI'              => $this->getGraph($id),
					'client'                => $sfClient,
					'sfConf'               => $uniqueComponentConf,
					'spec'                  => $uniqueComponentConf['spec'],
					'values'                => $this->getUriLinkUniqueElement($id),

				];

				// Same as FormType::class
				$uniqueComponentForm = $bundleName.'\Form\\'.ucfirst(
						$uniqueComponentName
					).'Type';
				//common
				/** @var \VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType $form */
				$form = $this->createForm(
					$uniqueComponentForm,
					$this->getUniqueElement($id),
					// Options.
					$options
				);
				$dataForWebPage = $this->specificTreatment($sfClient,$form,$request,$uniqueComponentName,$id);

				if( is_array($dataForWebPage )){
						$dataForWebPage["form"] = $form->createView();
						$dataForWebPage["entityUri"] = $this->getUriLinkUniqueElement($id);
						// Fill form
						return $this->render(
							$bundleName.':'.ucfirst($uniqueComponentName).':'.$uniqueComponentName.'Form.html.twig',
							$dataForWebPage
						);
				}
				else{
					return $dataForWebPage;
				}
		}

}
