<?php

declare(strict_types=1);

namespace App\IAM\Repository;

use App\IAM\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findValidByHash(string $tokenHash): ?RefreshToken
    {
        return $this->createQueryBuilder('rt')
            ->where('rt.tokenHash = :hash')
            ->andWhere('rt.revoked = false')
            ->andWhere('rt.expiresAt > :now')
            ->setParameter('hash', $tokenHash)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeAllForUser(string $userId): int
    {
        return $this->getEntityManager()->createQuery(
            'UPDATE App\IAM\Entity\RefreshToken rt SET rt.revoked = true
             WHERE rt.user = :userId AND rt.revoked = false'
        )
        ->setParameter('userId', $userId)
        ->execute();
    }

    public function deleteExpired(): int
    {
        return $this->getEntityManager()->createQuery(
            'DELETE FROM App\IAM\Entity\RefreshToken rt WHERE rt.expiresAt < :now'
        )
        ->setParameter('now', new \DateTimeImmutable())
        ->execute();
    }

    public function save(RefreshToken $token, bool $flush = false): void
    {
        $this->getEntityManager()->persist($token);
        if ($flush) $this->getEntityManager()->flush();
    }
}