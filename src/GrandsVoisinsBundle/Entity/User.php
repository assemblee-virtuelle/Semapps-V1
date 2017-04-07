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
     * @var string
     */
    private $sfUser;

    private $fkOrganisation;

    private $pictureName;

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

    /**
     * Set sfUser
     *
     * @param string $sfUser
     *
     * @return User
     */
    public function setSfUser($sfUser)
    {
        $this->sfUser = $sfUser;

        return $this;
    }

    /**
     * Get sfUser
     *
     * @return string
     */
    public function getSfUser()
    {
        return $this->sfUser;
    }

    /**
     * Set fkOrganisation
     *
     * @param integer $fkOrganisation
     *
     * @return User
     */
    public function setFkOrganisation($fkOrganisation)
    {
        $this->fkOrganisation = $fkOrganisation;

        return $this;
    }

    /**
     * Get fkOrganisation
     *
     * @return integer
     */
    public function getFkOrganisation()
    {
        return $this->fkOrganisation;
    }

    /**
     * Set pictureName
     *
     * @param string $pictureName
     *
     * @return User
     */
    public function setPictureName($pictureName)
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    /**
     * Get pictureName
     *
     * @return string
     */
    public function getPictureName()
    {
        return $this->pictureName;
    }
}
