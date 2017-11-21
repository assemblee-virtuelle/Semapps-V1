<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 13/11/2017
 * Time: 18:27
 */

namespace semappsBundle\Controller;


abstract class UniqueComponentController extends AbstractUniqueComponentController
{


		public function getGraph($id)
		{
				$user =$this->getUser();

				$organization =null;
				if($id != null && $user->hasRole(
						'ROLE_SUPER_ADMIN'
					) && $user->getFkOrganisation() != $id){
						$organization = $this->getOrga(
							$id
						);

				}
				else{
						$organization = $this->getOrga(
							$user->getFkOrganisation()
						);
				}
				return $organization->getGraphURI();
		}

		public function getSfUser($id)
		{

				$currentUser =$this->getUser();
				$userRepository = $this->getDoctrine()->getManager()->getRepository(
					'semappsBundle:User'
				);

				$organization =null;
				$sfUser = null;
				if($id != null && $currentUser->hasRole(
						'ROLE_SUPER_ADMIN'
					) && $currentUser->getFkOrganisation() != $id){
						$organization = $this->getOrga(
							$id
						);
						$responsable = $userRepository->find($organization->getFkResponsable());
						$sfUser = $responsable->getEmail();
				}
				else{
						$sfUser = $currentUser->getEmail();
				}
				return $sfUser;
		}

		public function getSfPassword($id)
		{
				/** @var \semappsBundle\Services\Encryption $encryption */
				$encryption = $this->container->get('semappsBundle.encryption');
				$currentUser =$this->getUser();
				$userRepository = $this->getDoctrine()->getManager()->getRepository(
					'semappsBundle:User'
				);

				$organization =null;
				$sfPassword = null;
				if($id != null && $currentUser->hasRole(
						'ROLE_SUPER_ADMIN'
					) && $currentUser->getFkOrganisation() != $id){
						$organization = $this->getOrga(
							$id
						);
						$responsable = $userRepository->find($organization->getFkResponsable());
						$sfPassword = $encryption->decrypt($responsable->getSfUser());

				}
				else{
						$sfPassword = $encryption->decrypt($currentUser->getSfUser());
				}
				return $sfPassword;
		}
		protected function getOrga($id){
				$organisationEntity = $this->getDoctrine()->getManager()->getRepository(
					'semappsBundle:Organisation'
				);
				$id = ($id != null)? $id : $this->getUser()->getFkOrganisation();
				$organization = $organisationEntity->find(
					$id
				);
				return $organization;
		}

}