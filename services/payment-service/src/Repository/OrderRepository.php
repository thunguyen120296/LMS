<?php

declare(strict_types=1);

namespace App\Payment\Repository;

use App\Payment\Entity\Order;
use App\Payment\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->findOneBy(['orderNumber' => $orderNumber]);
    }

    public function findWithItems(string $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.items', 'i')
            ->leftJoin('o.coupon', 'c')
            ->addSelect('i', 'c')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Order[] */
    public function findByUser(string $userId, ?OrderStatus $status = null, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($status !== null) {
            $qb->andWhere('o.status = :status')->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Checks if a user has already paid for a course.
     */
    public function hasPaidForCourse(string $userId, string $courseId): bool
    {
        $count = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->join('o.items', 'i')
            ->where('o.userId = :userId')
            ->andWhere('i.courseId = :courseId')
            ->andWhere('o.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('courseId', $courseId)
            ->setParameter('status', OrderStatus::Paid)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    /**
     * Revenue report: total paid per day in a date range.
     *
     * @return array<array{date: string, revenue: string, orders: int}>
     */
    public function getDailyRevenue(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->getEntityManager()->createNativeQuery(
            "SELECT
                DATE(created_at) AS date,
                SUM(total_amount) AS revenue,
                COUNT(*) AS orders
             FROM payment.orders
             WHERE status = 'paid'
               AND created_at BETWEEN :from AND :to
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            (new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->getEntityManager()))
        )
        ->setParameter('from', $from->format('Y-m-d H:i:s'))
        ->setParameter('to', $to->format('Y-m-d H:i:s'))
        ->getResult();
    }

    public function generateOrderNumber(): string
    {
        $date  = date('Ymd');
        $count = (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.orderNumber LIKE :prefix')
            ->setParameter('prefix', "ORD-{$date}-%")
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('ORD-%s-%05d', $date, $count + 1);
    }

    public function save(Order $order, bool $flush = false): void
    {
        $this->getEntityManager()->persist($order);
        if ($flush) $this->getEntityManager()->flush();
    }
}
