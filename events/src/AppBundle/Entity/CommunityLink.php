<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityLink
 *
 * @ORM\Table(name="community_link", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="community_link_community_idx", columns={"community_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityLinkRepository")
 */
class CommunityLink
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
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
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
}
