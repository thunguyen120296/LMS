<?php

namespace App\Repository;
 
use App\Entity\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<UserRole>
 */
class UserRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRole::class);
    }
 
    public function hasRole(string $userId, string $roleName): bool
    {
        $count = $this->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->join('ur.role', 'r')
            ->where('ur.user = :userId')
            ->andWhere('r.name = :roleName')
            ->setParameter('userId', $userId)
            ->setParameter('roleName', $roleName)
            ->getQuery()
            ->getSingleScalarResult();
 
        return (int) $count > 0;
    }
 
    public function save(UserRole $userRole, bool $flush = false): void
    {
        $this->getEntityManager()->persist($userRole);
        if ($flush) $this->getEntityManager()->flush();
    }
 
    public function remove(UserRole $userRole, bool $flush = false): void
    {
        $this->getEntityManager()->remove($userRole);
        if ($flush) $this->getEntityManager()->flush();
    }
}
