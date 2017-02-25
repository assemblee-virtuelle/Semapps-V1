<?php

namespace GrandsVoisinsBundle\Entity;

/**
 * Organisation
 */
class Organisation
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
    private $batiment;

    /**
     * @var string
     */
    private $sfOrganisation;


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
     * @return Organisation
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
     * Set batiment
     *
     * @param string $batiment
     *
     * @return Organisation
     */
    public function setBatiment($batiment)
    {
        $this->batiment = $batiment;

        return $this;
    }

    /**
     * Get batiment
     *
     * @return string
     */
    public function getBatiment()
    {
        return $this->batiment;
    }

    /**
     * Set sfOrganisation
     *
     * @param string $sfOrganisation
     *
     * @return Organisation
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
}

