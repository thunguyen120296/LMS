<?php

namespace App\Entity;

use App\Repository\UserRoleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ORM\Table(name: 'user_role', schema: 'iam')]
#[ORM\UniqueConstraint(name: 'uniq_user_role', columns: ['user_id', 'role_id'])]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false)]
    private ?Role $role = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $assignedAt = null;
}