<?php

namespace mmmfestBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMultipleComponentController extends AbstractComponentController
{
		abstract public function specificTreatment($sfClient,$form,$request,$componentName);
		abstract public function getGraphOfCurrentUser();
		abstract public function getSFLoginOfCurrentUser();
		abstract public function getSFPasswordOfCurrentUser();
		abstract public function getPathFormFolder();
		abstract public function getPathComponentView();

    public function listAction($componentName)
    {
				$componentList = $this->getParameter('semantic_forms.component');
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
				$componentConf = $this->getParameter($componentName.'Conf');
				$graphURI =$this->getGraphOfCurrentUser();

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);
        $graphURI = $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL);
        $componentType = $sparql->formatValue($componentList[$componentName],$sparql::VALUE_TYPE_URL);

				$sparql->addPrefixes($sparql->prefixes)
					->addSelect('?URI')
					->addWhere('?URI','rdf:type',$componentType,$graphURI);
				foreach ($componentConf['label'] as $field ){
						$label = $componentConf['fields'][$field]['value'];
						$fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
						$sparql->addSelect('?'.$label)
								->addWhere('?URI',$fieldFormatted,'?'.$label,$graphURI);
				}

				$results = $sfClient->sparql($sparql->getQuery());

        $listContent = [];
        if (isset($results["results"]["bindings"])) {
            foreach ($results["results"]["bindings"] as $item) {
            		$title = '';
								foreach ($componentConf['label'] as $field ){
										$label = $componentConf['fields'][$field]['value'];
										$title .= $item[$label]['value'] .' ';
								}
                $listContent[] = [
                  'uri'   => $item['URI']['value'],
                  'title' => $title,
                ];

            }
        }

        return $this->render(
          $this->getPathComponentView().$componentName.'List.html.twig',
          array(
            'componentName' => $componentName,
            'plural'        => $componentName.'(s)',
            'listContent'   => $listContent,
          )
        );
    }

    public function addAction($componentName,Request $request)
    {
        /** @var $user \mmmfestBundle\Entity\User */
        $sfClient     = $this->container->get('semantic_forms.client');
        $uri 					= $request->get('uri');
				$componentConf = $this->getParameter($componentName.'Conf');
				$graphURI			= $this->getGraphOfCurrentUser();

        // Same as FormType::class
        $componentClassName = $this->getPathFormFolder().ucfirst(
            $componentName
          ).'Type';

        $form = $this->createForm(
          $componentClassName,
          null,
          [
            'login'                 => $this->getSFLoginOfCurrentUser(),
            'password'              => $this->getSFPasswordOfCurrentUser(),
            'graphURI'              => $graphURI,
            'client'                => $sfClient,
            'sfConf'               => $componentConf,
            'spec'                  => $componentConf['spec'],
            'values'                => $uri,
            'lookupUrlLabel'        => $this->generateUrl(
              'webserviceFieldUriLabel'
            ),
            'lookupUrlPerson'       => $this->generateUrl(
              'webserviceFieldUriSearch'
            ),
            'lookupUrlOrganization' => $this->generateUrl(
              'webserviceFieldUriSearch'
            ),
          ]
        );
        // /!\ need to be array /!\
        $dataForWebPage = $this->specificTreatment($sfClient,$form,$request,$componentName);
        if( is_array($dataForWebPage )){
						$dataForWebPage["form"] = $form->createView();

						// Fill form
						return $this->render(
							$this->getPathComponentView().$componentName.'Form.html.twig',
							$dataForWebPage
						);
				}
				else{
        		return $dataForWebPage;
				}

    }

    public function removeAction($componentName,Request $request){
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
				$sparqlDeux = clone $sparql;

				$uri = $sparql->formatValue($request->get('uri'),$sparql::VALUE_TYPE_URL);

				$sparql->addDelete($uri,'?P','?O','?gr')
					->addWhere($uri,'?P','?O','?gr');
				$sparqlDeux->addDelete('?s','?PP',$uri,'?gr')
					->addWhere('?s','?PP',$uri,'?gr');

				$sfClient->update($sparql->getQuery());
				$sfClient->update($sparqlDeux->getQuery());


				return $this->redirectToRoute(
					'componentList', ["componentName" => $componentName]
				);
    }
}
