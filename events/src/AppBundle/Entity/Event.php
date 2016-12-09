<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="event_community_user_idx", columns={"community_user_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=45, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1000, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="permission", type="string", nullable=true)
     */
    private $permission;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_participant_num", type="integer", nullable=true)
     */
    private $maxParticipantNum;

    /**
     * @var float
     *
     * @ORM\Column(name="gps_location_lat", type="float", nullable=true)
     */
    private $gpsLocationLat;

    /**
     * @var float
     *
     * @ORM\Column(name="gps_location_lng", type="float", nullable=true)
     */
    private $gpsLocationLng;

    /**
     * @var string
     *
     * @ORM\Column(name="boarding_point", type="string", length=45, nullable=true)
     */
    private $boardingPoint;

    /**
     * @var string
     *
     * @ORM\Column(name="image_base64", type="text", nullable=true)
     */
    private $imageBase64;

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
     * Set title
     *
     * @param string $title
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
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
     * Set type
     *
     * @param integer $type
     * @return Event
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param string $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set maxParticipantNum
     *
     * @param integer $maxParticipantNum
     * @return Event
     */
    public function setMaxParticipantNum($maxParticipantNum)
    {
        $this->maxParticipantNum = $maxParticipantNum;

        return $this;
    }

    /**
     * Get maxParticipantNum
     *
     * @return integer 
     */
    public function getMaxParticipantNum()
    {
        return $this->maxParticipantNum;
    }

    /**
     * @return float
     */
    public function getGpsLocationLat()
    {
        return $this->gpsLocationLat;
    }

    /**
     * @param float $gpsLocationLat
     */
    public function setGpsLocationLat($gpsLocationLat)
    {
        $this->gpsLocationLat = $gpsLocationLat;
    }

    /**
     * @return float
     */
    public function getGpsLocationLng()
    {
        return $this->gpsLocationLng;
    }

    /**
     * @param float $gpsLocationLng
     */
    public function setGpsLocationLng($gpsLocationLng)
    {
        $this->gpsLocationLng = $gpsLocationLng;
    }

    /**
     * Set boardingPoint
     *
     * @param string $boardingPoint
     * @return Event
     */
    public function setBoardingPoint($boardingPoint)
    {
        $this->boardingPoint = $boardingPoint;

        return $this;
    }

    /**
     * Get boardingPoint
     *
     * @return string 
     */
    public function getBoardingPoint()
    {
        return $this->boardingPoint;
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
     * @return Event
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
