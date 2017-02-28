<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 28/02/2017
 * Time: 16:52
 */

namespace GrandsVoisinsBundle\Services;


use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use GrandsVoisinsBundle\Entity\Organisation;
use GrandsVoisinsBundle\Entity\User;

class GrandsVoisinsCommandServices
{
    private $em;
    private $generator;

    function __construct(EntityManager $em,TokenGeneratorInterface $generator)
    {
        $this->em = $em;
        $this->generator = $generator;
    }

    public function createOrganization($name,$buildings){
        $organization = new Organisation();
        $organization->setName($name);
        $organization->setBatiment($buildings);
        $this->em->persist($organization);
        $this->em->flush($organization);
        return $organization;
    }

    public function createUser($username,$email,array $roles, $fkOrganization){
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles($roles);

        $user->setFkOrganisation($fkOrganization);
        // Generate password.
       
        $randomPassword = substr($this->generator->generateToken(), 0, 12);
        $user->setPassword(
            password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 13])
        );

        $user->setSfUser($randomPassword);

        // Generate the token for the confirmation email
        $conf_token = $this->generator->generateToken();
        $user->setConfirmationToken($conf_token);

        $this->em->persist($user);
        $this->em->flush($user);
        return $user->getId();
    }

    public function updateOrganization(Organisation $organization, $userId){
        $organization->setFkResponsable($userId);
        $this->em->persist($organization);
        $this->em->flush($organization);
    }
}