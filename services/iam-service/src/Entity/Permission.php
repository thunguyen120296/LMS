<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions', schema: 'iam')]
#[ORM\UniqueConstraint(name: 'uq_iam_permission', columns: ['resource', 'action'])]
class Permission
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // e.g. "course", "enrollment", "payment"
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $resource;

    // e.g. "read", "write", "delete", "publish"
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $action;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'permission', targetEntity: RolePermission::class, cascade: ['remove'])]
    private Collection $rolePermissions;

    public function __construct()
    {
        $this->rolePermissions = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }
    public function getResource(): string { return $this->resource; }
    public function setResource(string $resource): static { $this->resource = $resource; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $desc): static { $this->description = $desc; return $this; }

    public function getKey(): string
    {
        return $this->resource . ':' . $this->action;
    }
}