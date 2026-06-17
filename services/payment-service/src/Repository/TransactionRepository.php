<?php

declare(strict_types=1);

namespace App\Payment\Repository;

use App\Payment\Entity\Transaction;
use App\Payment\Enum\TransactionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByProviderTxnId(string $provider, string $providerTxnId): ?Transaction
    {
        return $this->findOneBy(['provider' => $provider, 'providerTxnId' => $providerTxnId]);
    }

    public function findLastByOrder(string $orderId): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->where('t.order = :orderId')
            ->setParameter('orderId', $orderId)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Transaction[] */
    public function findSuccessfulByOrder(string $orderId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.order = :orderId')
            ->andWhere('t.status = :status')
            ->setParameter('orderId', $orderId)
            ->setParameter('status', TransactionStatus::Success)
            ->getQuery()
            ->getResult();
    }

    public function save(Transaction $txn, bool $flush = false): void
    {
        $this->getEntityManager()->persist($txn);
        if ($flush) $this->getEntityManager()->flush();
    }
}
