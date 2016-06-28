<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UniversityMail
 *
 * @ORM\Table(name="university_mail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UniversityMailRepository")
 */
class UniversityMail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hostname", type="string", length=75, unique=true, nullable=false)
     */
    private $hostname;

    /**
     * @var \AppBundle\Entity\University
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\University" )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="university_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $university;

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
     * Set hostname
     *
     * @param string $hostname
     * @return UniversityMail
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string 
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return University
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * @param University $university
     */
    public function setUniversity($university)
    {
        $this->university = $university;
    }
}
