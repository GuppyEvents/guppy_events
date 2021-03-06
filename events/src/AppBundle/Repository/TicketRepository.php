<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Ticket;
use Doctrine\ORM\EntityRepository;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TicketRepository extends EntityRepository
{

    /**
     *
     * @param array $eventId The event id
     * @return Ticket|null
     */
    public function findLowestPriceTicketByEventId($eventId)
    {
        return $this->createQueryBuilder('ticket')
            ->where('ticket.event =:eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('ticket.price', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

}
