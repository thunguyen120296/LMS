<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    /** @return Role[] */
    public function findAllWithPermissions(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.rolePermissions', 'rp')
            ->leftJoin('rp.permission', 'p')
            ->addSelect('rp', 'p')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Role $role, bool $flush = false): void
    {
        $this->getEntityManager()->persist($role);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(Role $role, bool $flush = false): void
    {
        $this->getEntityManager()->remove($role);
        if ($flush) $this->getEntityManager()->flush();
    }
}