<?php

declare(strict_types=1);

namespace App\Enrollment\Repository;

use App\Enrollment\Entity\LessonProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LessonProgress>
 */
class LessonProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonProgress::class);
    }

    public function findByEnrollmentAndLesson(string $enrollmentId, string $lessonId): ?LessonProgress
    {
        return $this->findOneBy(['enrollment' => $enrollmentId, 'lessonId' => $lessonId]);
    }

    /**
     * @return LessonProgress[]
     */
    public function findCompletedByEnrollment(string $enrollmentId): array
    {
        return $this->createQueryBuilder('lp')
            ->where('lp.enrollment = :enrollmentId')
            ->andWhere('lp.isCompleted = true')
            ->setParameter('enrollmentId', $enrollmentId)
            ->getQuery()
            ->getResult();
    }

    public function countCompletedByEnrollment(string $enrollmentId): int
    {
        return (int) $this->createQueryBuilder('lp')
            ->select('COUNT(lp.id)')
            ->where('lp.enrollment = :enrollmentId')
            ->andWhere('lp.isCompleted = true')
            ->setParameter('enrollmentId', $enrollmentId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns lesson IDs completed by a user across ALL enrollments for a course.
     * Used to restore progress when enrollment is recreated.
     *
     * @return string[]
     */
    public function findCompletedLessonIdsByUserAndCourse(string $userId, string $courseId): array
    {
        $result = $this->createQueryBuilder('lp')
            ->select('lp.lessonId')
            ->join('lp.enrollment', 'e')
            ->where('e.userId = :userId')
            ->andWhere('e.courseId = :courseId')
            ->andWhere('lp.isCompleted = true')
            ->setParameter('userId', $userId)
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'lessonId');
    }

    public function save(LessonProgress $progress, bool $flush = false): void
    {
        $this->getEntityManager()->persist($progress);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(LessonProgress $progress, bool $flush = false): void
    {
        $this->getEntityManager()->remove($progress);
        if ($flush) $this->getEntityManager()->flush();
    }
}