<?php

namespace App\Repository;

use App\Entity\PasswordVault;
use App\Entity\PasswordVaultEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordVaultEntry>
 */
final class PasswordVaultEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordVaultEntry::class);
    }

    /**
     * @return PasswordVaultEntry[]
     */
    public function findByVaultOrdered(PasswordVault $vault): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.vault = :vault')
            ->setParameter('vault', $vault)
            ->orderBy('e.updatedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByVaultAndFingerprint(PasswordVault $vault, string $fingerprint): ?PasswordVaultEntry
    {
        return $this->findOneBy([
            'vault' => $vault,
            'entryFingerprint' => $fingerprint,
        ]);
    }
}
