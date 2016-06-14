<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="address", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class Address
{
    /**
     * @var string
     *
     * @ORM\Column(name="country_id", type="string", length=45, nullable=true)
     */
    private $countryId;

    /**
     * @var string
     *
     * @ORM\Column(name="city_id", type="string", length=45, nullable=true)
     */
    private $cityId;

    /**
     * @var string
     *
     * @ORM\Column(name="borough_id", type="string", length=45, nullable=true)
     */
    private $boroughId;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=45, nullable=true)
     */
    private $state;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set countryId
     *
     * @param string $countryId
     * @return Address
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * Get countryId
     *
     * @return string 
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * Set cityId
     *
     * @param string $cityId
     * @return Address
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return string 
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set boroughId
     *
     * @param string $boroughId
     * @return Address
     */
    public function setBoroughId($boroughId)
    {
        $this->boroughId = $boroughId;

        return $this;
    }

    /**
     * Get boroughId
     *
     * @return string 
     */
    public function getBoroughId()
    {
        return $this->boroughId;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Address
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
