<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 13/11/2017
 * Time: 18:27
 */

namespace semappsBundle\Controller;


abstract class UniqueComponentController extends AbstractComponentController
{
		public function getGraph($id=null){
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

		public function getSfUser($id = null )
		{
				return  $this->getUser()->getEmail();
		}

		public function getSfPassword($id = null)
		{
				/** @var \semappsBundle\Services\Encryption $encryption */
				$encryption = $this->container->get('semappsBundle.encryption');
				return $encryption->decrypt($this->getUser()->getSfUser());
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