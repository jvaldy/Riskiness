<?php

namespace App\Repository;

use App\Entity\CycleCareEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CycleCareEvent>
 */
final class CycleCareEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CycleCareEvent::class);
    }

    /**
     * @return CycleCareEvent[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.user = :user')
            ->setParameter('user', $user)
            ->orderBy('event.eventDate', 'DESC')
            ->addOrderBy('event.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?CycleCareEvent
    {
        return $this->findOneBy(['user' => $user, 'id' => $id]);
    }
}
