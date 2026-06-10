<?php

namespace App\Entity;

use App\Repository\RolePermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
#[ORM\Table(name: 'role_permission', schema: 'iam')]
#[ORM\UniqueConstraint(name: 'uniq_role_permission', columns: ['role_id', 'permission_id'])]
class RolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Role $role = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Permission $permission = null;
}