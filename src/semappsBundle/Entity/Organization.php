<?php

namespace semappsBundle\Entity;

/**
 * Organization
 */
class Organization
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $sfOrganisation;

    private $graphURI;

    private $fkResponsable;

    private $organisationPicture;

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
     * Set name
     *
     * @param string $name
     *
     * @return Organization
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set sfOrganisation
     *
     * @param string $sfOrganisation
     *
     * @return Organization
     */
    public function setSfOrganisation($sfOrganisation)
    {
        $this->sfOrganisation = $sfOrganisation;

        return $this;
    }

    /**
     * Get sfOrganisation
     *
     * @return string
     */
    public function getSfOrganisation()
    {
        return $this->sfOrganisation;
    }

    /**
     * Set fkResponsable
     *
     * @param integer $fkResponsable
     *
     * @return Organization
     */
    public function setFkResponsable($fkResponsable)
    {
        $this->fkResponsable = $fkResponsable;

        return $this;
    }

    /**
     * Get fkResponsable
     *
     * @return integer
     */
    public function getFkResponsable()
    {
        return $this->fkResponsable;
    }

    /**
     * Set graphURI
     *
     * @param string $graphURI
     *
     * @return Organization
     */
    public function setGraphURI($graphURI)
    {
        $this->graphURI = $graphURI;

        return $this;
    }

    /**
     * Get graphURI
     *
     * @return string
     */
    public function getGraphURI()
    {
        return $this->graphURI;
    }

    /**
     * Set OrganisationPicture
     *
     * @param string $organisation_picture
     *
     * @return Organization
     */
    public function setOrganisationPicture($organisationPicture)
    {
        $this->organisationPicture = $organisationPicture;

        return $this;
    }

    /**
     * Get OrganisationPicture
     *
     * @return string
     */
    public function getOrganisationPicture()
    {
        return $this->organisationPicture;
    }

}
