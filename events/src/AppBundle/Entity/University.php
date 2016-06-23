<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * University
 *
 * @ORM\Table(name="university", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UniversityRepository")
 */
class University
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=45, nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=45, nullable=true)
     */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     * })
     */
    private $address;



    /**
     * Set name
     *
     * @param string $name
     * @return University
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
     * Set link
     *
     * @param string $link
     * @return University
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return University
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
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

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     * @return University
     */
    public function setAddress(\AppBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address 
     */
    public function getAddress()
    {
        return $this->address;
    }
}
