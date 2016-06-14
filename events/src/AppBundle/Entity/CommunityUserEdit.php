<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityUserEdit
 *
 * @ORM\Table(name="community_user_edit", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="community_user_edit_community_user_idx", columns={"community_user_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityUserEditRepository")
 */
class CommunityUserEdit
{
    /**
     * @var string
     *
     * @ORM\Column(name="prev_name", type="string", length=45, nullable=true)
     */
    private $prevName;

    /**
     * @var string
     *
     * @ORM\Column(name="prev_description", type="string", length=250, nullable=true)
     */
    private $prevDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="prev_image_url", type="string", length=250, nullable=true)
     */
    private $prevImageUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\CommunityUser
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommunityUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="community_user_id", referencedColumnName="id")
     * })
     */
    private $communityUser;



    /**
     * Set prevName
     *
     * @param string $prevName
     * @return CommunityUserEdit
     */
    public function setPrevName($prevName)
    {
        $this->prevName = $prevName;

        return $this;
    }

    /**
     * Get prevName
     *
     * @return string 
     */
    public function getPrevName()
    {
        return $this->prevName;
    }

    /**
     * Set prevDescription
     *
     * @param string $prevDescription
     * @return CommunityUserEdit
     */
    public function setPrevDescription($prevDescription)
    {
        $this->prevDescription = $prevDescription;

        return $this;
    }

    /**
     * Get prevDescription
     *
     * @return string 
     */
    public function getPrevDescription()
    {
        return $this->prevDescription;
    }

    /**
     * Set prevImageUrl
     *
     * @param string $prevImageUrl
     * @return CommunityUserEdit
     */
    public function setPrevImageUrl($prevImageUrl)
    {
        $this->prevImageUrl = $prevImageUrl;

        return $this;
    }

    /**
     * Get prevImageUrl
     *
     * @return string 
     */
    public function getPrevImageUrl()
    {
        return $this->prevImageUrl;
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
     * Set communityUser
     *
     * @param \AppBundle\Entity\CommunityUser $communityUser
     * @return CommunityUserEdit
     */
    public function setCommunityUser(\AppBundle\Entity\CommunityUser $communityUser = null)
    {
        $this->communityUser = $communityUser;

        return $this;
    }

    /**
     * Get communityUser
     *
     * @return \AppBundle\Entity\CommunityUser 
     */
    public function getCommunityUser()
    {
        return $this->communityUser;
    }
}
