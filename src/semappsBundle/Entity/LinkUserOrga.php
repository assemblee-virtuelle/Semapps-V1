<?php

namespace semappsBundle\Entity;

/**
 * LinkUserOrga
 */
class LinkUserOrga
{
    const ROLE_DEFAULT = 'ROLE_USER';
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
     * @return array
     */
    public function setRoles(array $roles)
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this->roles;
    }

    /**
     * Get roles
     *
     * @return array
     */

    public function getRoles()
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }


}

