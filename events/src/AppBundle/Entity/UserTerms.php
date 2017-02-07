<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserTerms
 *
 * @ORM\Table(name="user_terms", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="user_terms_user_idx", columns={"user_id"}), @ORM\Index(name="user_terms_terms_idx", columns={"terms_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserTermsRepository")
 */
class UserTerms extends Base
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
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Terms
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Terms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="terms_id", referencedColumnName="id")
     * })
     */
    private $terms;


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
     * @return UserTerms
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
     * @return Terms
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param Terms $terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

}
