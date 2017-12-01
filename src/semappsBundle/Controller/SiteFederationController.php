<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 30/11/2017
 * Time: 16:49
 */

namespace semappsBundle\Controller;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use semappsBundle\Form\SiteType;
use semappsBundle\semappsConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class SiteFederationController extends Controller
{
	public function manageSiteAction(Request $request){

			// Find all users.
			$siteManager = $this->getDoctrine()
				->getManager()
				->getRepository(
					'semappsBundle:Site'
				);

			$sites       = $siteManager->findAll();

			/** @var Form $form */
			$form        = $this->get('form.factory')->create(
				SiteType::class
			);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
					// Get posted data of type user
					$newSite = $form->getData();
					// Save it.
					$em = $this->getDoctrine()->getManager();
					$em->persist($newSite);
					try {
							$em->flush();
					} catch (UniqueConstraintViolationException $e) {
							$this->addFlash('danger', "le site saisi existe déjà");

							return $this->redirectToRoute('team');
					}
					// Go back to team page.
					return $this->redirectToRoute('team');
			}

			return $this->render(
				'semappsBundle:Site:site.html.twig',
				array(
					'sites'     => $sites,
					'formSite'  => $form->createView(),
				)
			);
	}
}