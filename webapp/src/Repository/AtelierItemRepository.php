<?php

namespace App\Repository;

use App\Entity\AtelierItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AtelierItem>
 */
final class AtelierItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AtelierItem::class);
    }

    /**
     * @return AtelierItem[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.user = :user')
            ->setParameter('user', $user)
            ->orderBy('item.isFavorite', 'DESC')
            ->addOrderBy('item.updatedAt', 'DESC')
            ->addOrderBy('item.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?AtelierItem
    {
        return $this->findOneBy([
            'user' => $user,
            'id' => $id,
        ]);
    }
}
