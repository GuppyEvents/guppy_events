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
    public function findCommunityListByName($keyValue , $page=1 ,$pageSize=10)
    {
        return $this->createQueryBuilder('community')
            ->join('community.university', 'university')
            ->where('community.name LIKE :nameKeyValue AND university.id = :universityId AND community.isApproved = true')
            ->setParameter('nameKeyValue', '%'.$keyValue.'%')
            ->setParameter('universityId', 5)
            ->orderBy('community.name', 'ASC')
            ->setFirstResult($pageSize * ($page - 1))
            ->setMaxResults( $pageSize )
            ->getQuery()
            ->getResult();
    }


    /**
     *
     * @param string $keyValue The community name key value
     * @return Community|null
     */
    public function findCommunityListByUniversity($page=1 ,$pageSize=24,$universityId=5)
    {
        return $this->createQueryBuilder('community')
            ->join('community.university', 'university')
            ->where('university.id = :universityId AND community.isApproved = true')
            ->setParameter('universityId', $universityId)
            ->orderBy('community.name', 'ASC')
            ->setFirstResult($pageSize * ($page - 1))
            ->setMaxResults( $pageSize )
            ->getQuery()
            ->getResult();
    }


    /**
     *
     * @param string $communityId The community id value
     * @return Community|null
     */
    public function findOnePublishCommunity($communityId)
    {
        return $this->createQueryBuilder('community')
            ->where('community.id= :communityId')
            ->andWhere('community.isApproved=true')
            ->andWhere('community.deleteDate is null')
            ->setParameter('communityId', $communityId)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
