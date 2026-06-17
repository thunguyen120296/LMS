<?php

declare(strict_types=1);

namespace App\Payment\Repository;

use App\Payment\Entity\Payout;
use App\Payment\Enum\PayoutStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payout>
 */
class PayoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payout::class);
    }

    /** @return Payout[] */
    public function findByInstructor(string $instructorId, ?PayoutStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.instructorId = :id')
            ->setParameter('id', $instructorId)
            ->orderBy('p.requestedAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('p.status = :status')->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Payout[] */
    public function findPending(): array
    {
        return $this->findBy(['status' => PayoutStatus::Pending], ['requestedAt' => 'ASC']);
    }

    /**
     * Total amount paid out to an instructor (completed only).
     */
    public function getTotalPaidOut(string $instructorId): string
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.netAmount)')
            ->where('p.instructorId = :id')
            ->andWhere('p.status = :status')
            ->setParameter('id', $instructorId)
            ->setParameter('status', PayoutStatus::Completed)
            ->getQuery()
            ->getSingleScalarResult();

        return number_format((float) ($result ?? 0), 2, '.', '');
    }

    public function save(Payout $payout, bool $flush = false): void
    {
        $this->getEntityManager()->persist($payout);
        if ($flush) $this->getEntityManager()->flush();
    }
}