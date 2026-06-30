<?php

namespace App\Repository;

use App\Entity\MovieTrackerEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieTrackerEntry>
 */
final class MovieTrackerEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieTrackerEntry::class);
    }

    /**
     * @return MovieTrackerEntry[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.user = :user')
            ->setParameter('user', $user)
            ->orderBy('entry.updatedAt', 'DESC')
            ->addOrderBy('entry.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?MovieTrackerEntry
    {
        return $this->findOneBy([
            'user' => $user,
            'id' => $id,
        ]);
    }
}
