<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Community
 *
 * @ORM\Table(name="community", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="community_university_idx", columns={"university_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityRepository")
 */
class Community extends Base
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=5000, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     */
    private $imageUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_approved", type="boolean", nullable=true)
     */
    private $isApproved;

    /**
     * @var string
     *
     * @ORM\Column(name="image_base64", type="text", nullable=true)
     */
    private $imageBase64;

    /**
     * @var string
     *
     * @ORM\Column(name="image_background_base64", type="text", nullable=true)
     */
    private $imageBackgroundBase64;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\University
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\University")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="university_id", referencedColumnName="id")
     * })
     */
    private $university;


    public function __construct()
    {
        $this->isApproved = false;
    }


    /**
     * Set name
     *
     * @param string $name
     * @return Community
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
     * Set description
     *
     * @param string $description
     * @return Community
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     * @return Community
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string 
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set isApproved
     *
     * @param boolean $isApproved
     * @return Community
     */
    public function setIsApproved($isApproved)
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    /**
     * Get isApproved
     *
     * @return boolean 
     */
    public function getIsApproved()
    {
        return $this->isApproved;
    }

    /**
     * @return string
     */
    public function getImageBase64()
    {
        return $this->imageBase64;
    }

    /**
     * @param string $imageBase64
     */
    public function setImageBase64($imageBase64)
    {
        $this->imageBase64 = $imageBase64;
    }

    /**
     * @return string
     */
    public function getImageBackgroundBase64()
    {
        return $this->imageBackgroundBase64;
    }

    /**
     * @param string $imageBackgroundBase64
     */
    public function setImageBackgroundBase64($imageBackgroundBase64)
    {
        $this->imageBackgroundBase64 = $imageBackgroundBase64;
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
     * Set university
     *
     * @param \AppBundle\Entity\University $university
     * @return Community
     */
    public function setUniversity(\AppBundle\Entity\University $university = null)
    {
        $this->university = $university;

        return $this;
    }

    /**
     * Get university
     *
     * @return \AppBundle\Entity\University 
     */
    public function getUniversity()
    {
        return $this->university;
    }
}
