<?php

declare(strict_types=1);

namespace App\Repository;
 
use App\Entity\CourseLearningObjective;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<CourseLearningObjective>
 */
class CourseLearningObjectiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseLearningObjective::class);
    }
 
    /** @return CourseLearningObjective[] */
    public function findByCourse(string $courseId): array
    {
        return $this->createQueryBuilder('lo')
            ->where('lo.course = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('lo.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
 
    public function deleteAllByCourse(string $courseId): void
    {
        $this->getEntityManager()->createQuery(
            'DELETE FROM App\Entity\CourseLearningObjective lo WHERE lo.course = :courseId'
        )
        ->setParameter('courseId', $courseId)
        ->execute();
    }
 
    public function save(CourseLearningObjective $objective, bool $flush = false): void
    {
        $this->getEntityManager()->persist($objective);
        if ($flush) $this->getEntityManager()->flush();
    }
}