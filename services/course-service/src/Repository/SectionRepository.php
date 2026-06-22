<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    /**
     * Returns all sections of a course with their lessons eagerly loaded.
     *
     * @return Section[]
     */
    public function findByCourseWithLessons(string $courseId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.lessons', 'l')
            ->addSelect('l')
            ->where('s.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('l.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all sections ordered by sortOrder (no lessons loaded).
     *
     * @return Section[]
     */
    public function findByCourse(string $courseId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('s.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the highest sortOrder in a course, or -1 if none.
     */
    public function findMaxSortOrder(string $courseId): int
    {
        $result = $this->createQueryBuilder('s')
            ->select('MAX(s.sortOrder)')
            ->where('s.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) $result : -1;
    }

    /**
     * Shift sortOrder of all sections after a given position by +1 or -1.
     */
    public function shiftSortOrder(string $courseId, int $fromPosition, int $delta = 1): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Section s
             SET s.sortOrder = s.sortOrder + :delta
             WHERE s.course = :courseId
             AND s.sortOrder >= :from'
        )
        ->setParameter('courseId', $courseId)
        ->setParameter('from', $fromPosition)
        ->setParameter('delta', $delta)
        ->execute();
    }

    public function save(Section $section, bool $flush = false): void
    {
        $this->getEntityManager()->persist($section);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Section $section, bool $flush = false): void
    {
        $this->getEntityManager()->remove($section);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}