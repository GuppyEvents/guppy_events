<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommunityUser;
use Doctrine\ORM\EntityRepository;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommunityUserRepository extends EntityRepository
{

    /**
     *
     * @return CommunityUser|null
     */
    public function findAllExceptAdmin()
    {
        return $this->createQueryBuilder('communityUser')
            ->where('communityUser.user != :adminId')
            ->setParameter('adminId', 18)
            ->getQuery()
            ->getResult();
    }


    /**
     *
     * @return integer|0
     */
    public function findCommunityAdminCount()
    {
        return $this->createQueryBuilder('communityUser')
            ->where('communityUser.status = :statusVal')
            ->setParameter('statusVal', 1)
            ->distinct()
            ->getQuery()
            ->getResult();
    }


    /**
     *
     * @return integer|0
     */
    public function findCommunityMembersCount()
    {
        return $this->createQueryBuilder('communityUser')
            ->where('communityUser.status = :statusVal')
            ->setParameter('statusVal', 2)
            ->distinct()
            ->getQuery()
            ->getResult();
    }


    /**
     *
     * @return integer|0
     */
    public function findCommunityUserRequestCount()
    {
        return $this->createQueryBuilder('communityUser')
            ->where('communityUser.status = :statusVal')
            ->setParameter('statusVal', 10)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

}
