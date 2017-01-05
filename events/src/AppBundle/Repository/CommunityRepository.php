<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommunityRepository extends EntityRepository
{

    /**
     *
     * @param string $keyValue The community name key value
     * @return Community|null
     */
    public function findCommunityListByName($keyValue)
    {
        return $this->createQueryBuilder('community')
            ->join('community.university', 'university')
            ->where('community.name LIKE :nameKeyValue AND university.id = :universityId')
            ->setParameter('nameKeyValue', '%'.$keyValue.'%')
            ->setParameter('universityId', 5)
            ->getQuery()
            ->getResult();
    }

}
