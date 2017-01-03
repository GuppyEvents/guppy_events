<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityUser
 *
 * @ORM\Table(name="community_user", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="community_user_user_idx", columns={"user_id"}), @ORM\Index(name="community_user_community_idx", columns={"community_id"}), @ORM\Index(name="community_user_perform_byx", columns={"perform_by"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityUserRepository")
 */
class CommunityUser extends Base
{
    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status=10;

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
     * @var \AppBundle\Entity\Community
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="community_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $community;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="perform_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $performBy;


    /**
     * Set status
     *
     * @param integer $status
     * @return CommunityUser
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return CommunityUser
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
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
     * @return CommunityUser
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
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     * @return CommunityUser
     */
    public function setCommunity(\AppBundle\Entity\Community $community = null)
    {
        $this->community = $community;

        return $this;
    }

    /**
     * Get community
     *
     * @return \AppBundle\Entity\Community 
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @return User
     */
    public function getPerformBy()
    {
        return $this->performBy;
    }

    /**
     * @param User $performBy
     */
    public function setPerformBy($performBy)
    {
        $this->performBy = $performBy;
    }
}
