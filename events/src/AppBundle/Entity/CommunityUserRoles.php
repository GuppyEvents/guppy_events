<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityUserRoles
 *
 * @ORM\Table(name="community_user_roles", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}) , @ORM\UniqueConstraint(name="community_user_role_UNIQUE", columns={"community_user_id","community_role_id"})}, indexes={@ORM\Index(name="community_user_roles_community_user_idx", columns={"community_user_id"}), @ORM\Index(name="community_user_roles_community_role_idx", columns={"community_role_id"}), @ORM\Index(name="community_user_roles_state_idx", columns={"state"}), @ORM\Index(name="community_user_roles_perform_byx", columns={"perform_by"}) })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityUserRolesRepository")
 */
class CommunityUserRoles extends Base
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1000, nullable=true)
     */
    private $description;

    /**
     * @var \AppBundle\Entity\CommunityUserRoleState
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommunityUserRoleState")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state", referencedColumnName="id", nullable=false)
     * })
     */
    private $state;

    /**
     * @var \AppBundle\Entity\CommunityUser
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommunityUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="community_user_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $communityUser;

    /**
     * @var \AppBundle\Entity\CommunityRole
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommunityRole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="community_role_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $communityRole;


    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="perform_by", referencedColumnName="id", nullable=false)
     * })
     */
    private $performBy;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return CommunityUserRoleState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param CommunityUserRoleState $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return CommunityUser
     */
    public function getCommunityUser()
    {
        return $this->communityUser;
    }

    /**
     * @param CommunityUser $communityUser
     */
    public function setCommunityUser($communityUser)
    {
        $this->communityUser = $communityUser;
    }

    /**
     * @return CommunityRole
     */
    public function getCommunityRole()
    {
        return $this->communityRole;
    }

    /**
     * @param CommunityRole $communityRole
     */
    public function setCommunityRole($communityRole)
    {
        $this->communityRole = $communityRole;
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
