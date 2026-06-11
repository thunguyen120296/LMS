<?php
namespace App\Repository;
 
use App\Entity\RolePermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<RolePermission>
 */
class RolePermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePermission::class);
    }
 
    public function save(RolePermission $rp, bool $flush = false): void
    {
        $this->getEntityManager()->persist($rp);
        if ($flush) $this->getEntityManager()->flush();
    }
 
    public function remove(RolePermission $rp, bool $flush = false): void
    {
        $this->getEntityManager()->remove($rp);
        if ($flush) $this->getEntityManager()->flush();
    }
}