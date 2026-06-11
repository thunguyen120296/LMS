<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens', schema: 'iam')]
#[ORM\Index(columns: ['token_hash'], name: 'idx_iam_refresh_token_hash')]
#[ORM\Index(columns: ['user_id', 'revoked'], name: 'idx_iam_refresh_user_revoked')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'refreshTokens')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $tokenHash;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $deviceInfo = null;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $revoked = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, string $tokenHash, \DateTimeImmutable $expiresAt)
    {
        $this->user       = $user;
        $this->tokenHash  = $tokenHash;
        $this->expiresAt  = $expiresAt;
        $this->createdAt  = new \DateTimeImmutable();
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }

    public function isValid(): bool
    {
        return !$this->revoked && $this->expiresAt > new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getTokenHash(): string { return $this->tokenHash; }
    public function getDeviceInfo(): ?string { return $this->deviceInfo; }
    public function setDeviceInfo(?string $info): static { $this->deviceInfo = $info; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ip): static { $this->ipAddress = $ip; return $this; }
    public function isRevoked(): bool { return $this->revoked; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}