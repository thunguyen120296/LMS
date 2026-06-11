<?php
 
declare(strict_types=1);
 
namespace App\Repository;
 
use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<Permission>
 */
class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }
 
    public function findByResourceAction(string $resource, string $action): ?Permission
    {
        return $this->findOneBy(['resource' => $resource, 'action' => $action]);
    }
 
    /** @return Permission[] */
    public function findByResource(string $resource): array
    {
        return $this->findBy(['resource' => $resource]);
    }
 
    public function save(Permission $permission, bool $flush = false): void
    {
        $this->getEntityManager()->persist($permission);
        if ($flush) $this->getEntityManager()->flush();
    }
}