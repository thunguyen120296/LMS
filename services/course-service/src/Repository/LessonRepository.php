<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lesson;
use App\Enum\LessonType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * Returns a lesson with its section and course loaded — used for access-control checks.
     */
    public function findWithCourse(string $lessonId): ?Lesson
    {
        return $this->createQueryBuilder('l')
            ->join('l.section', 's')
            ->join('s.course', 'c')
            ->addSelect('s', 'c')
            ->where('l.id = :id')
            ->setParameter('id', $lessonId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns all published lessons of a course across all sections.
     * Used to build a flat lesson list for progress tracking.
     *
     * @return Lesson[]
     */
    public function findPublishedByCourse(string $courseId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.section', 's')
            ->where('s.course = :courseId')
            ->andWhere('l.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('l.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns free-preview lessons of a course — accessible without enrollment.
     *
     * @return Lesson[]
     */
    public function findFreePreviewByCourse(string $courseId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.section', 's')
            ->where('s.course = :courseId')
            ->andWhere('l.isFreePreview = true')
            ->andWhere('l.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('l.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns lessons by type in a course — e.g. all video or quiz lessons.
     *
     * @return Lesson[]
     */
    public function findByTypeInCourse(string $courseId, LessonType $type): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.section', 's')
            ->where('s.course = :courseId')
            ->andWhere('l.type = :type')
            ->andWhere('l.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->setParameter('type', $type)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('l.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the highest sortOrder within a section.
     */
    public function findMaxSortOrder(string $sectionId): int
    {
        $result = $this->createQueryBuilder('l')
            ->select('MAX(l.sortOrder)')
            ->where('l.section = :sectionId')
            ->setParameter('sectionId', $sectionId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) $result : -1;
    }

    /**
     * Shifts sortOrder for lessons after a given position.
     */
    public function shiftSortOrder(string $sectionId, int $fromPosition, int $delta = 1): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Lesson l
             SET l.sortOrder = l.sortOrder + :delta
             WHERE l.section = :sectionId
             AND l.sortOrder >= :from'
        )
        ->setParameter('sectionId', $sectionId)
        ->setParameter('from', $fromPosition)
        ->setParameter('delta', $delta)
        ->execute();
    }

    /**
     * Count total published lessons for a course — used to update Course.totalLessons.
     */
    public function countPublishedByCourse(string $courseId): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->join('l.section', 's')
            ->where('s.course = :courseId')
            ->andWhere('l.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sum of video durations in seconds for a course.
     */
    public function sumVideoDurationByCourse(string $courseId): int
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.videoDurationSec)')
            ->join('l.section', 's')
            ->where('s.course = :courseId')
            ->andWhere('l.isPublished = true')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    public function save(Lesson $lesson, bool $flush = false): void
    {
        $this->getEntityManager()->persist($lesson);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lesson $lesson, bool $flush = false): void
    {
        $this->getEntityManager()->remove($lesson);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}