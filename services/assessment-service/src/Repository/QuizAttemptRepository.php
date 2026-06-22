<?php

declare(strict_types=1);

namespace App\Assessment\Repository;

use App\Assessment\Entity\QuizAttempt;
use App\Assessment\Enum\AttemptStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<QuizAttempt> */
class QuizAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAttempt::class);
    }

    public function findActiveAttempt(string $quizId, string $userId): ?QuizAttempt
    {
        return $this->createQueryBuilder('a')
            ->join('a.quiz', 'q')
            ->where('q.id = :quizId')
            ->andWhere('a.userId = :userId')
            ->andWhere('a.status = :status')
            ->setParameter('quizId', $quizId)
            ->setParameter('userId', $userId)
            ->setParameter('status', AttemptStatus::InProgress)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAttempts(string $quizId, string $userId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.quiz', 'q')
            ->where('q.id = :quizId')
            ->andWhere('a.userId = :userId')
            ->andWhere('a.status != :inProgress')
            ->setParameter('quizId', $quizId)
            ->setParameter('userId', $userId)
            ->setParameter('inProgress', AttemptStatus::InProgress)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return QuizAttempt[] */
    public function findByUser(string $userId, int $page = 1, int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.startedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function save(QuizAttempt $attempt, bool $flush = false): void
    {
        $this->getEntityManager()->persist($attempt);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
