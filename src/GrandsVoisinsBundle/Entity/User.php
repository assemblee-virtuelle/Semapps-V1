<?php

namespace GrandsVoisinsBundle\Entity;
use FOS\UserBundle\Model\User as BaseUser;
/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    private $sfLink;
    

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sfLink
     *
     * @param string $sfLink
     *
     * @return User
     */
    public function setSfLink($sfLink)
    {
        $this->sfLink = $sfLink;

        return $this;
    }

    /**
     * Get sfLink
     *
     * @return string
     */
    public function getSfLink()
    {
        return $this->sfLink;
    }
}

