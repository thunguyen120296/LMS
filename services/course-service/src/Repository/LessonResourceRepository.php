<?php
 
declare(strict_types=1);
 
namespace App\Repository;
 
use App\Entity\LessonResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<LessonResource>
 */
class LessonResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonResource::class);
    }
 
    /**
     * @return LessonResource[]
     */
    public function findByLesson(string $lessonId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.lesson = :lessonId')
            ->setParameter('lessonId', $lessonId)
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
 
    public function save(LessonResource $resource, bool $flush = false): void
    {
        $this->getEntityManager()->persist($resource);
        if ($flush) $this->getEntityManager()->flush();
    }
 
    public function remove(LessonResource $resource, bool $flush = false): void
    {
        $this->getEntityManager()->remove($resource);
        if ($flush) $this->getEntityManager()->flush();
    }
}