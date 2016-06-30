<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UniversityUser
 *
 * @ORM\Table(name="university_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UniversityUserRepository")
 */
class UniversityUser extends Base
{
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=false)
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_validated", type="boolean", nullable=true)
     */
    private $isValidated;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var \AppBundle\Entity\UniversityMail
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UniversityMail")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="university_mail_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $universityMail;


    /**
     * Set email
     *
     * @param string $email
     * @return UniversityUser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isValidated
     *
     * @param boolean $isValidated
     * @return UniversityUser
     */
    public function setIsValidated($isValidated)
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    /**
     * Get isValidated
     *
     * @return boolean 
     */
    public function getIsValidated()
    {
        return $this->isValidated;
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return UniversityUser
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return UniversityMail
     */
    public function getUniversityMail()
    {
        return $this->universityMail;
    }

    /**
     * @param UniversityMail $universityMail
     */
    public function setUniversityMail($universityMail)
    {
        $this->universityMail = $universityMail;
    }

}
