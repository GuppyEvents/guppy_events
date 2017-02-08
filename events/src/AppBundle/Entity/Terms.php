<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Terms
 *
 * @ORM\Table(name="terms", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TermsRepository")
 */
class Terms extends Base
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
     * @ORM\Column(name="terms_of_use", type="text", nullable=false)
     */
    private $termsOfUse;



    /**
     * Set termsOfUse
     *
     * @param string $termsOfUse
     * @return Badge
     */
    public function setTermsOfUse($termsOfUse)
    {
        $this->termsOfUse = $termsOfUse;

        return $this;
    }

    /**
     * Get termsOfUse
     *
     * @return string
     */
    public function getTermsOfUse()
    {
        return $this->termsOfUse;
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
}
