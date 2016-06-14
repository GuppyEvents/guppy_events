<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserBadge
 *
 * @ORM\Table(name="user_badge", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="user_badge_user_idx", columns={"user_id"}), @ORM\Index(name="user_badge_badge_idx", columns={"badge_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserBadgeRepository")
 */
class UserBadge
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="earn_date", type="datetime", nullable=false)
     */
    private $earnDate;

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
     * @var \AppBundle\Entity\Badge
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Badge")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="badge_id", referencedColumnName="id")
     * })
     */
    private $badge;



    /**
     * Set earnDate
     *
     * @param \DateTime $earnDate
     * @return UserBadge
     */
    public function setEarnDate($earnDate)
    {
        $this->earnDate = $earnDate;

        return $this;
    }

    /**
     * Get earnDate
     *
     * @return \DateTime 
     */
    public function getEarnDate()
    {
        return $this->earnDate;
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
     * @return UserBadge
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
     * Set badge
     *
     * @param \AppBundle\Entity\Badge $badge
     * @return UserBadge
     */
    public function setBadge(\AppBundle\Entity\Badge $badge = null)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get badge
     *
     * @return \AppBundle\Entity\Badge 
     */
    public function getBadge()
    {
        return $this->badge;
    }
}
