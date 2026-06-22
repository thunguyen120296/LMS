<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NotificationChannel;
use App\Repository\UserPreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
#[ORM\Table(name: 'user_preferences', schema: 'notification')]
class UserPreference
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref → iam.users */
    #[ORM\Column(type: 'uuid', unique: true)]
    private string $userId;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $emailEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $pushEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $marketingEnabled = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $userId)
    {
        $this->userId    = $userId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function allows(NotificationChannel $channel): bool
    {
        return match ($channel) {
            NotificationChannel::Email => $this->emailEnabled,
            NotificationChannel::Push  => $this->pushEnabled,
            NotificationChannel::InApp => true,
        };
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function isEmailEnabled(): bool { return $this->emailEnabled; }
    public function setEmailEnabled(bool $enabled): static { $this->emailEnabled = $enabled; $this->touch(); return $this; }
    public function isPushEnabled(): bool { return $this->pushEnabled; }
    public function setPushEnabled(bool $enabled): static { $this->pushEnabled = $enabled; $this->touch(); return $this; }
    public function isMarketingEnabled(): bool { return $this->marketingEnabled; }
    public function setMarketingEnabled(bool $enabled): static { $this->marketingEnabled = $enabled; $this->touch(); return $this; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
