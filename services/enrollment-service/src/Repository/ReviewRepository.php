<?php

declare(strict_types=1);

namespace App\Enrollment\Repository;

use App\Enrollment\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByEnrollment(string $enrollmentId): ?Review
    {
        return $this->findOneBy(['enrollment' => $enrollmentId]);
    }

    /**
     * @return Review[]
     */
    public function findPublishedByCourse(string $courseId, int $page = 1, int $limit = 20): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.courseId = :courseId')
            ->andWhere('r.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns avg rating and count for a course — used to update Course denormalized fields.
     *
     * @return array{avg: float, total: int}
     */
    public function getStatsByCourse(string $courseId): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg, COUNT(r.id) as total')
            ->where('r.courseId = :courseId')
            ->andWhere('r.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleResult();

        return [
            'avg'   => round((float) ($result['avg'] ?? 0), 1),
            'total' => (int) ($result['total'] ?? 0),
        ];
    }

    /**
     * Rating distribution e.g. [5 => 120, 4 => 80, ...]
     *
     * @return array<int, int>
     */
    public function getRatingDistribution(string $courseId): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.id) as cnt')
            ->where('r.courseId = :courseId')
            ->andWhere('r.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->groupBy('r.rating')
            ->getQuery()
            ->getResult();

        $dist = array_fill(1, 5, 0);
        foreach ($rows as $row) {
            $dist[(int) $row['rating']] = (int) $row['cnt'];
        }

        return $dist;
    }

    public function save(Review $review, bool $flush = false): void
    {
        $this->getEntityManager()->persist($review);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(Review $review, bool $flush = false): void
    {
        $this->getEntityManager()->remove($review);
        if ($flush) $this->getEntityManager()->flush();
    }
}
