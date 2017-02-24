<?php

namespace GrandsVoisinsBundle\Entity;

/**
 * LinkUserOrga
 */
class LinkUserOrga
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $fkOrga;

    /**
     * @var int
     */
    private $fkUser;

    /**
     * @var array
     */
    private $roles;


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
     * Set fkOrga
     *
     * @param integer $fkOrga
     *
     * @return LinkUserOrga
     */
    public function setFkOrga($fkOrga)
    {
        $this->fkOrga = $fkOrga;

        return $this;
    }

    /**
     * Get fkOrga
     *
     * @return int
     */
    public function getFkOrga()
    {
        return $this->fkOrga;
    }

    /**
     * Set fkUser
     *
     * @param integer $fkUser
     *
     * @return LinkUserOrga
     */
    public function setFkUser($fkUser)
    {
        $this->fkUser = $fkUser;

        return $this;
    }

    /**
     * Get fkUser
     *
     * @return int
     */
    public function getFkUser()
    {
        return $this->fkUser;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return LinkUserOrga
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}

