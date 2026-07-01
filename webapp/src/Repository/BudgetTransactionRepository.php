<?php

namespace App\Repository;

use App\Entity\BudgetTransaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetTransaction>
 */
final class BudgetTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetTransaction::class);
    }

    /**
     * @return BudgetTransaction[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('transaction')
            ->andWhere('transaction.user = :user')
            ->setParameter('user', $user)
            ->orderBy('transaction.occurredAt', 'DESC')
            ->addOrderBy('transaction.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?BudgetTransaction
    {
        return $this->findOneBy([
            'user' => $user,
            'id' => $id,
        ]);
    }
}
