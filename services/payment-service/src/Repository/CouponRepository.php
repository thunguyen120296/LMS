<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Coupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coupon>
 */
class CouponRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupon::class);
    }

    public function findValidByCode(string $code): ?Coupon
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('c')
            ->where('c.code = :code')
            ->andWhere('c.isActive = true')
            ->andWhere('c.startsAt IS NULL OR c.startsAt <= :now')
            ->andWhere('c.expiresAt IS NULL OR c.expiresAt >= :now')
            ->setParameter('code', strtoupper($code))
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Coupon $coupon, bool $flush = false): void
    {
        $this->getEntityManager()->persist($coupon);
        if ($flush) $this->getEntityManager()->flush();
    }
}
