<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Revenue per course — for instructor earnings summary.
     *
     * @return array<array{courseId: string, courseTitle: string, revenue: string, sales: int}>
     */
    public function getRevenuePerCourse(string $instructorId): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.courseId, i.courseTitle, SUM(i.finalPrice) AS revenue, COUNT(i.id) AS sales')
            ->join('i.order', 'o')
            ->where('o.status = :status')
            ->andWhere('i.courseId IN (
                SELECT c.id FROM App\Course\Entity\Course c WHERE c.instructorId = :instructorId
            )')
            ->setParameter('status', \App\Enum\OrderStatus::Paid)
            ->setParameter('instructorId', $instructorId)
            ->groupBy('i.courseId, i.courseTitle')
            ->orderBy('revenue', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(OrderItem $item, bool $flush = false): void
    {
        $this->getEntityManager()->persist($item);
        if ($flush) $this->getEntityManager()->flush();
    }
}