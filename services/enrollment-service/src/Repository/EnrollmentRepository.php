<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enrollment;
use App\Enum\EnrollmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrollment>
 */
class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    public function findByUserAndCourse(string $userId, string $courseId): ?Enrollment
    {
        return $this->findOneBy(['userId' => $userId, 'courseId' => $courseId]);
    }

    public function findActiveByUserAndCourse(string $userId, string $courseId): ?Enrollment
    {
        return $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->andWhere('e.courseId = :courseId')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('userId', $userId)
            ->setParameter('courseId', $courseId)
            ->setParameter('statuses', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns enrollment with all lesson progresses — used for progress dashboard.
     */
    public function findWithProgress(string $enrollmentId): ?Enrollment
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.lessonProgresses', 'lp')
            ->addSelect('lp')
            ->where('e.id = :id')
            ->setParameter('id', $enrollmentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * All courses a user is enrolled in — for "My Learning" page.
     *
     * @return Enrollment[]
     */
    public function findByUser(string $userId, ?EnrollmentStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('e.enrolledAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Enrollment[]
     */
    public function findByCourse(string $courseId, ?EnrollmentStatus $status = null, int $page = 1, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.courseId = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('e.enrolledAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($status !== null) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByCourse(string $courseId, ?EnrollmentStatus $status = null): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.courseId = :courseId')
            ->setParameter('courseId', $courseId);

        if ($status !== null) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Check if user is already enrolled (regardless of status).
     */
    public function isEnrolled(string $userId, string $courseId): bool
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.userId = :userId')
            ->andWhere('e.courseId = :courseId')
            ->setParameter('userId', $userId)
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * Find enrollments expiring soon — for notification jobs.
     *
     * @return Enrollment[]
     */
    public function findExpiringSoon(int $daysAhead = 7): array
    {
        $threshold = new \DateTimeImmutable("+{$daysAhead} days");

        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.expiredAt IS NOT NULL')
            ->andWhere('e.expiredAt <= :threshold')
            ->setParameter('status', EnrollmentStatus::Active)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    public function save(Enrollment $enrollment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($enrollment);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(Enrollment $enrollment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($enrollment);
        if ($flush) $this->getEntityManager()->flush();
    }
}