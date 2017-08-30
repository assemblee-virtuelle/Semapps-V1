<?php

namespace mmmfestBundle\Controller;


use mmmfestBundle\mmmfestConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ComponentController extends Controller
{

    var $componentName = 'undefined';
    var $pluralName = 'undefined';
    var $sparqlPrefix = 'undefined';

    public function listAction()
    {
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
          'mmmfestBundle:Organisation'
        );

        $organisation = $organisationEntity->find(
          $this->getUser()->getFkOrganisation()
        );

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);
        $graphURI = $sparql->formatValue($organisation->getGraphURI(),$sparql::VALUE_TYPE_URL);
        $sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default','http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
            ->addSelect('?URI ?NAME')
            ->addWhere('?URI','rdf:type',$this->sparqlPrefix,$graphURI)
            ->addWhere('?URI','default:preferedLabel','?NAME',$graphURI);
        $results = $sfClient->sparql($sparql->getQuery());

        $listContent = [];
        if (isset($results["results"]["bindings"])) {
            foreach ($results["results"]["bindings"] as $item) {
                $listContent[] = [
                  'uri'   => $item['URI']['value'],
                  'title' => $item['NAME']['value'],
                ];
            }
        }

        return $this->render(
          'mmmfestBundle:Component:'.$this->componentName.'List.html.twig',
          array(
            'componentName' => $this->componentName,
            'plural'        => $this->pluralName,
            'listContent'   => $listContent,
          )
        );
    }

    public function addAction(Request $request)
    {
        /** @var \mmmfestBundle\Services\Encryption $encryption */
        $encryption 	= $this->container->get('mmmfestBundle.encryption');
        /** @var $user \mmmfestBundle\Entity\User */
        $user         = $this->getUser();
        $sfClient     = $this->container->get('semantic_forms.client');
				/** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient = $this->container->get('sparqlbundle.client');
        $uri 					= $request->get('uri');
        $organisation = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('mmmfestBundle:Organisation')
          ->find($this->getUser()->getFkOrganisation());

        // Same as FormType::class
        $componentClassName = 'mmmfestBundle\Form\\'.ucfirst(
            $this->componentName
          ).'Type';

        $specName = 'SPEC_'.strtoupper($request->get('component'));

        $form = $this->createForm(
          $componentClassName,
          null,
          [
            'login'                 => $user->getEmail(),
            'password'              => $encryption->decrypt($user->getSfUser()),
            'graphURI'              => $organisation->getGraphURI(),
            'client'                => $sfClient,
            'reverse'               => mmmfestConfig::REVERSE,
            'spec'                  => constant(
              'mmmfestBundle\mmmfestConfig::'.$specName
            ),
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
				// Remove old picture.
				$fileUploader = $this->get('mmmfestBundle.fileUploader');
				$pictureDir = $fileUploader->getTargetDir();
        //actualPicture
				$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);
				$sparql->addPrefixes($sparql->prefixes)
					->addPrefix('default', 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
					->addSelect('?oldImage')
					->addWhere(
						$sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
						'default:image',
						'?oldImage',
						$sparql->formatValue($organisation->getGraphURI(), $sparql::VALUE_TYPE_URL));
				$results = $sfClient->sparql($sparql->getQuery());
				$actualImage = $sfClient->sparqlResultsValues($results);
				$actualImageName = null;
				if (!empty($actualImage)) {
						$cutUrl = explode("/", $actualImage[0]['oldImage']);
						$actualImageName = $cutUrl[sizeof($cutUrl) - 1];
				}
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
						// Manage picture.
						if($form->has('componentPicture')){
								$newPicture = $form->get('componentPicture')->getData();
								if ($newPicture) {

										if ($actualImageName) {
												// Check if file exists to avoid all errors.
												if (is_file($pictureDir . '/' . $actualImageName)) {
														$fileUploader->remove($actualImageName);
												}
										}
										$newPictureName = $fileUploader->upload($newPicture);

										$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
										$sparql->addPrefixes($sparql->prefixes)
											->addPrefix('default', 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
											->addDelete(
												$sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
												'default:image',
												'?o',
												$sparql->formatValue($organisation->getGraphURI(), $sparql::VALUE_TYPE_URL))
											->addWhere(
												$sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
												'default:image',
												'?o',
												$sparql->formatValue($organisation->getGraphURI(), $sparql::VALUE_TYPE_URL));
										$sfClient->update($sparql->getQuery());

										$sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_INSERT_DATA);
										$sparql->addPrefixes($sparql->prefixes)
											->addPrefix('default', 'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#')
											->addInsert(
												$sparql->formatValue($uri, $sparql::VALUE_TYPE_URL),
												'default:image',
												$sparql->formatValue($fileUploader->generateUrlForFile($newPictureName), $sparql::VALUE_TYPE_TEXT),
												$sparql->formatValue($organisation->getGraphURI(), $sparql::VALUE_TYPE_URL));
										$sfClient->update($sparql->getQuery());
										$actualImageName = $newPictureName;
								}
						}
						$this->addFlash('info', 'Le contenu à bien été mis à jour.');
            return $this->redirectToRoute(
              strtolower($request->get('component')).'List'
            );
        }
        // Fill form
        return $this->render(
          'mmmfestBundle:Component:'.$this->componentName.'Form.html.twig',
          array(
            'form' => $form->createView(),
            'image' => $actualImageName
          )
        );
    }
    public function removeAction(){

        $route = [
          'project' => 'projet',
          'event' => 'evenement',
          'proposal' => 'proposal',
					'document' => 'document',
					'documenttype' => 'documentType'
          ];
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');
        $componentName = $_GET['componentName'];
        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
        $uri = $sparql->formatValue($_GET['uri'],$sparql::VALUE_TYPE_URL);
        $sparql->addDelete($uri,'?P','?O','?gr')
            ->addDelete('?s','?PP',$uri,'?gr')
            ->addWhere($uri,'?P','?O','?gr');
            //->addWhere('?s','?PP',$uri,'?gr');
        $sfClient->update($sparql->getQuery());

        return $this->redirect('/mon-compte/'.$route[$componentName]);
    }
}
