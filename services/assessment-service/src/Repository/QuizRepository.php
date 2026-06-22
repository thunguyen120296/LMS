<?php

declare(strict_types=1);

namespace App\Assessment\Repository;

use App\Assessment\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Quiz> */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    public function findPublishedById(string $id): ?Quiz
    {
        return $this->createQueryBuilder('q')
            ->where('q.id = :id')
            ->andWhere('q.isPublished = true')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Quiz[] */
    public function findByCourse(string $courseId, bool $publishedOnly = true): array
    {
        $qb = $this->createQueryBuilder('q')
            ->where('q.courseId = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('q.createdAt', 'DESC');

        if ($publishedOnly) {
            $qb->andWhere('q.isPublished = true');
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithQuestions(string $id): ?Quiz
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.questions', 'qu')
            ->leftJoin('qu.options', 'opt')
            ->addSelect('qu', 'opt')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Quiz $quiz, bool $flush = false): void
    {
        $this->getEntityManager()->persist($quiz);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
