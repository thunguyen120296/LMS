<?php

declare(strict_types=1);

namespace App\Course\Repository;

use App\Course\Entity\Course;
use App\Course\Enum\CourseLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    // ------------------------------------------------------------------
    // Single-record finders
    // ------------------------------------------------------------------

    public function findPublishedById(string $id): ?Course
    {
        return $this->createActivePublishedQB()
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->createActivePublishedQB()
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Full course detail: sections → lessons → resources + tags loaded eagerly.
     */
    public function findWithFullCurriculum(string $id): ?Course
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.sections', 's')
            ->leftJoin('s.lessons', 'l')
            ->leftJoin('l.resources', 'r')
            ->leftJoin('c.courseTags', 'ct')
            ->leftJoin('ct.tag', 't')
            ->leftJoin('c.learningObjectives', 'lo')
            ->leftJoin('c.requirements', 'req')
            ->addSelect('s', 'l', 'r', 'ct', 't', 'lo', 'req')
            ->where('c.id = :id')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // ------------------------------------------------------------------
    // List / paginated finders
    // ------------------------------------------------------------------

    /**
     * @return Course[]
     */
    public function findPublished(
        int $page = 1,
        int $limit = 20,
        ?string $categoryId = null,
        ?string $search = null,
        ?CourseLevel $level = null,
        ?string $language = null,
        string $orderBy = 'totalStudents',
        string $direction = 'DESC',
    ): array {
        $qb = $this->createActivePublishedQB()
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat');

        if ($categoryId !== null) {
            $qb->andWhere('c.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($search !== null) {
            $qb->andWhere('c.title LIKE :search OR c.shortDescription LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($level !== null) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $level);
        }

        if ($language !== null) {
            $qb->andWhere('c.language = :language')
               ->setParameter('language', $language);
        }

        $allowedOrder = ['totalStudents', 'avgRating', 'publishedAt', 'price'];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'totalStudents';
        }

        return $qb->orderBy('c.' . $orderBy, strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countPublished(
        ?string $categoryId = null,
        ?string $search = null,
        ?CourseLevel $level = null,
    ): int {
        $qb = $this->createActivePublishedQB()
            ->select('COUNT(c.id)');

        if ($categoryId !== null) {
            $qb->andWhere('c.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($search !== null) {
            $qb->andWhere('c.title LIKE :search OR c.shortDescription LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($level !== null) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $level);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Course[]
     */
    public function findByInstructor(string $instructorId, bool $includeUnpublished = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.instructorId = :instructorId')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('instructorId', $instructorId)
            ->orderBy('c.createdAt', 'DESC');

        if (!$includeUnpublished) {
            $qb->andWhere('c.isPublished = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $ids
     * @return Course[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Course[]
     */
    public function findTopRated(int $limit = 10, ?string $categoryId = null): array
    {
        $qb = $this->createActivePublishedQB()
            ->andWhere('c.totalReviews >= 5')
            ->orderBy('c.avgRating', 'DESC')
            ->addOrderBy('c.totalStudents', 'DESC')
            ->setMaxResults($limit);

        if ($categoryId !== null) {
            $qb->andWhere('c.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Course[]
     */
    public function findNewlyPublished(int $limit = 10): array
    {
        return $this->createActivePublishedQB()
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // ------------------------------------------------------------------
    // Stats helpers (used by event handlers to update denormalized fields)
    // ------------------------------------------------------------------

    public function incrementStudentCount(string $courseId): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Course\Entity\Course c
             SET c.totalStudents = c.totalStudents + 1, c.updatedAt = :now
             WHERE c.id = :id'
        )
        ->setParameter('id', $courseId)
        ->setParameter('now', new \DateTimeImmutable())
        ->execute();
    }

    public function decrementStudentCount(string $courseId): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Course\Entity\Course c
             SET c.totalStudents = GREATEST(c.totalStudents - 1, 0), c.updatedAt = :now
             WHERE c.id = :id'
        )
        ->setParameter('id', $courseId)
        ->setParameter('now', new \DateTimeImmutable())
        ->execute();
    }

    public function updateRatingStats(string $courseId, float $avgRating, int $totalReviews): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Course\Entity\Course c
             SET c.avgRating = :avg, c.totalReviews = :total, c.updatedAt = :now
             WHERE c.id = :id'
        )
        ->setParameter('id', $courseId)
        ->setParameter('avg', $avgRating)
        ->setParameter('total', $totalReviews)
        ->setParameter('now', new \DateTimeImmutable())
        ->execute();
    }

    // ------------------------------------------------------------------
    // Slug uniqueness check
    // ------------------------------------------------------------------

    public function isSlugTaken(string $slug, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.slug = :slug')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    // ------------------------------------------------------------------
    // Persist helpers
    // ------------------------------------------------------------------

    public function save(Course $course, bool $flush = false): void
    {
        $this->getEntityManager()->persist($course);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Course $course, bool $flush = false): void
    {
        $this->getEntityManager()->remove($course);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function createActivePublishedQB(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.isPublished = true');
    }
}