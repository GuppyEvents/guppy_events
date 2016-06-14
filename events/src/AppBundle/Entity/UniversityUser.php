<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UniversityUser
 *
 * @ORM\Table(name="university_user", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="university_user_user_idx", columns={"user_id"}), @ORM\Index(name="university_user_university_idx", columns={"university_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UniversityUserRepository")
 */
class UniversityUser
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
     * @var \DateTime
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=true)
     */
    private $registerDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delete_date", type="datetime", nullable=true)
     */
    private $deleteDate;

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
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \AppBundle\Entity\University
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\University")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="university_id", referencedColumnName="id")
     * })
     */
    private $university;



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
     * Set registerDate
     *
     * @param \DateTime $registerDate
     * @return UniversityUser
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * Get registerDate
     *
     * @return \DateTime 
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     * @return UniversityUser
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set deleteDate
     *
     * @param \DateTime $deleteDate
     * @return UniversityUser
     */
    public function setDeleteDate($deleteDate)
    {
        $this->deleteDate = $deleteDate;

        return $this;
    }

    /**
     * Get deleteDate
     *
     * @return \DateTime 
     */
    public function getDeleteDate()
    {
        return $this->deleteDate;
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
     * Set university
     *
     * @param \AppBundle\Entity\University $university
     * @return UniversityUser
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
