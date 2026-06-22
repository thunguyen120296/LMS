<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Certificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificate>
 */
class CertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificate::class);
    }

    public function findByNumber(string $certificateNumber): ?Certificate
    {
        return $this->findOneBy(['certificateNumber' => $certificateNumber]);
    }

    public function findByEnrollment(string $enrollmentId): ?Certificate
    {
        return $this->findOneBy(['enrollment' => $enrollmentId]);
    }

    /** @return Certificate[] */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function generateNextNumber(): string
    {
        $year  = date('Y');
        $count = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.certificateNumber LIKE :prefix')
            ->setParameter('prefix', "UC-{$year}-%")
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('UC-%s-%08d', $year, $count + 1);
    }

    public function save(Certificate $certificate, bool $flush = false): void
    {
        $this->getEntityManager()->persist($certificate);
        if ($flush) $this->getEntityManager()->flush();
    }
}
