<?php

declare(strict_types=1);

namespace App\IAM\Entity;

use App\IAM\Repository\RolePermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
#[ORM\Table(name: 'role_permissions', schema: 'iam')]
#[ORM\UniqueConstraint(name: 'uq_iam_role_permission', columns: ['role_id', 'permission_id'])]
class RolePermission
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'rolePermissions')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\ManyToOne(targetEntity: Permission::class, inversedBy: 'rolePermissions')]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Permission $permission;

    public function __construct(Role $role, Permission $permission)
    {
        $this->role       = $role;
        $this->permission = $permission;
    }

    public function getId(): string { return $this->id; }
    public function getRole(): Role { return $this->role; }
    public function getPermission(): Permission { return $this->permission; }
}