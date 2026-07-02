<?php

namespace App\Repository;

use App\Entity\DocSentinelDocument;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocSentinelDocument>
 */
final class DocSentinelDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocSentinelDocument::class);
    }

    /**
     * @return DocSentinelDocument[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('document')
            ->andWhere('document.user = :user')
            ->setParameter('user', $user)
            ->orderBy('document.archived', 'ASC')
            ->addOrderBy('document.expirationDate', 'ASC')
            ->addOrderBy('document.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndId(User $user, int $id): ?DocSentinelDocument
    {
        return $this->findOneBy([
            'user' => $user,
            'id' => $id,
        ]);
    }
}
