<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StateRepository extends EntityRepository
{
    /**
     *
     * @return StateState|null
     */
    public function findPendingState()
    {
        return $this->createQueryBuilder('state')
            ->where('state.id=1001')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     *
     * @return State|null
     */
    public function findPublishState()
    {
        return $this->createQueryBuilder('state')
            ->where('state.id=1002')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     *
     * @return State|null
     */
    public function findUnpublishState()
    {
        return $this->createQueryBuilder('state')
            ->where('state.id=1003')
            ->getQuery()
            ->getOneOrNullResult();
    }
    
}