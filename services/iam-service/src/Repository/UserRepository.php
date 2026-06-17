<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findActiveById(string $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->andWhere('u.deletedAt IS NULL')
            ->andWhere('u.isActive = true')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email, 'deletedAt' => null]);
    }

    public function findBySso(string $provider, string $subject): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.ssoProvider = :provider')
            ->andWhere('u.ssoSubject = :subject')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('provider', $provider)
            ->setParameter('subject', $subject)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithRoles(string $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userRoles', 'ur')
            ->leftJoin('ur.role', 'r')
            ->addSelect('ur', 'r')
            ->where('u.id = :id')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithRolesAndPermissionsByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userRoles', 'ur')->addSelect('ur')
            ->leftJoin('ur.role', 'r')->addSelect('r')
            ->leftJoin('r.rolePermissions', 'rp')->addSelect('rp')
            ->leftJoin('rp.permission', 'p')->addSelect('p')
            ->where('u.email = :email')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return User[]
     */
    public function findPaginated(int $page, int $limit, ?string $search = null): array
    {
        $qb = $this->createActiveQueryBuilder();

        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.username LIKE :search OR u.firstName LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countActive(): int
    {
        return (int) $this->createActiveQueryBuilder()
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createActiveQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL');
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}