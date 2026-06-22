<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NotificationChannel;
use App\Enum\NotificationStatus;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications', schema: 'notification')]
#[ORM\Index(columns: ['user_id', 'status', 'created_at'], name: 'idx_notification_inbox')]
#[ORM\HasLifecycleCallbacks]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref → iam.users */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $templateCode;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: NotificationChannel::class)]
    private NotificationChannel $channel;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $body;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: NotificationStatus::class)]
    private NotificationStatus $status = NotificationStatus::Pending;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $userId,
        string $templateCode,
        NotificationChannel $channel,
        string $title,
        string $body,
        ?array $payload = null,
    ) {
        $this->userId       = $userId;
        $this->templateCode = $templateCode;
        $this->channel      = $channel;
        $this->title        = $title;
        $this->body         = $body;
        $this->payload      = $payload;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markSent(): void
    {
        $this->status = NotificationStatus::Sent;
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status = NotificationStatus::Failed;
    }

    public function markRead(): void
    {
        $this->status = NotificationStatus::Read;
        $this->readAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getTemplateCode(): string { return $this->templateCode; }
    public function getChannel(): NotificationChannel { return $this->channel; }
    public function getTitle(): string { return $this->title; }
    public function getBody(): string { return $this->body; }

    /** @return array<string, mixed>|null */
    public function getPayload(): ?array { return $this->payload; }

    public function getStatus(): NotificationStatus { return $this->status; }
    public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
