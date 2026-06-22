<?php

declare(strict_types=1);

namespace App\Assessment\Repository;

use App\Assessment\Entity\QuizAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<QuizAnswer> */
class QuizAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAnswer::class);
    }
}
