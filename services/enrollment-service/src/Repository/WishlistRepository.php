<?php

declare(strict_types=1);

namespace App\Enrollment\Repository;

use App\Enrollment\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function findByUserAndCourse(string $userId, string $courseId): ?Wishlist
    {
        return $this->findOneBy(['userId' => $userId, 'courseId' => $courseId]);
    }

    /** @return Wishlist[] */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('w.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return string[] */
    public function findCourseIdsByUser(string $userId): array
    {
        $result = $this->createQueryBuilder('w')
            ->select('w.courseId')
            ->where('w.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'courseId');
    }

    public function isWishlisted(string $userId, string $courseId): bool
    {
        return $this->findByUserAndCourse($userId, $courseId) !== null;
    }

    public function save(Wishlist $wishlist, bool $flush = false): void
    {
        $this->getEntityManager()->persist($wishlist);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(Wishlist $wishlist, bool $flush = false): void
    {
        $this->getEntityManager()->remove($wishlist);
        if ($flush) $this->getEntityManager()->flush();
    }
}