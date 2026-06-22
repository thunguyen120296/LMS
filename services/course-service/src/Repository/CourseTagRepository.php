<?php

declare(strict_types=1);

namespace App\Repository;
 
use App\Entity\CourseTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<CourseTag>
 */
class CourseTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseTag::class);
    }
 
    /**
     * Replaces all tags for a course atomically.
     *
     * @param string[] $tagIds
     */
    public function syncTagsForCourse(string $courseId, array $tagIds): void
    {
        $em = $this->getEntityManager();
 
        // Delete existing
        $em->createQuery(
            'DELETE FROM App\Entity\CourseTag ct WHERE ct.course = :courseId'
        )
        ->setParameter('courseId', $courseId)
        ->execute();
 
        // Re-insert
        foreach ($tagIds as $tagId) {
            $em->createNativeQuery(
                'INSERT INTO course.course_tags (course_id, tag_id) VALUES (:courseId, :tagId) ON CONFLICT DO NOTHING',
                new \Doctrine\ORM\Query\ResultSetMapping()
            )
            ->setParameter('courseId', $courseId)
            ->setParameter('tagId', $tagId)
            ->execute();
        }
    }
 
    public function save(CourseTag $courseTag, bool $flush = false): void
    {
        $this->getEntityManager()->persist($courseTag);
        if ($flush) $this->getEntityManager()->flush();
    }
 
    public function remove(CourseTag $courseTag, bool $flush = false): void
    {
        $this->getEntityManager()->remove($courseTag);
        if ($flush) $this->getEntityManager()->flush();
    }
}