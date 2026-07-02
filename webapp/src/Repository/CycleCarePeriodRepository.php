<?php

namespace App\Repository;

use App\Entity\CycleCarePeriod;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CycleCarePeriod>
 */
final class CycleCarePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CycleCarePeriod::class);
    }

    /**
     * @return CycleCarePeriod[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('period')
            ->andWhere('period.user = :user')
            ->setParameter('user', $user)
            ->orderBy('period.startDate', 'DESC')
            ->addOrderBy('period.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?CycleCarePeriod
    {
        return $this->findOneBy(['user' => $user, 'id' => $id]);
    }
}
