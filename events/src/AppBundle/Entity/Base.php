<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base Entity
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks
 */
abstract class Base
{

    /**
     * @var \DateTime $registerDate
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=true)
     */
    private $registerDate;

    /**
     * @var \DateTime $updateDate
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @var \DateTime $deleteDate
     *
     * @ORM\Column(name="delete_date", type="datetime", nullable=true)
     */
    private $deleteDate;



    /**
     * @return DateTime
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * @param DateTime $registerDate
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;
    }

    /**
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return DateTime
     */
    public function getDeleteDate()
    {
        return $this->deleteDate;
    }

    /**
     * @param DateTime $deleteDate
     */
    public function setDeleteDate($deleteDate)
    {
        $this->deleteDate = $deleteDate;
    }


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        $this->setUpdateDate(new \DateTime('now'));

        if ($this->getRegisterDate() == null) {
            $this->setRegisterDate(new \DateTime('now'));
        }
    }

}