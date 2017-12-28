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
            'semappsBundle:Organization'
        );
        if ($id)
            return $organisationEntity->find($id);
        else
            return $organisationEntity->find($this->getUser()->getFkOrganisation());
    }

    protected function getOrgaByGraph($graph){
        $organisationEntity = $this->getDoctrine()->getManager()->getRepository(
            'semappsBundle:Organization'
        );
        if ($graph)
            return $organisationEntity->findOneBy(['graphURI'=> $graph]);
        else
            return null;
    }

}