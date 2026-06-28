<?php

namespace App\Repository;

use App\Entity\PasswordVault;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordVault>
 */
final class PasswordVaultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordVault::class);
    }

    public function findOneByUser(User $user): ?PasswordVault
    {
        return $this->findOneBy(['user' => $user]);
    }
}
