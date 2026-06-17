<?php

declare(strict_types=1);

namespace App\Enrollment\Entity;

use App\Enrollment\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'reviews', schema: 'enrollment')]
#[ORM\Index(columns: ['course_id', 'is_published', 'rating'], name: 'idx_enrollment_reviews_course')]
#[ORM\Index(columns: ['user_id'], name: 'idx_enrollment_reviews_user')]
#[ORM\HasLifecycleCallbacks]
class Review
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\OneToOne(targetEntity: Enrollment::class, inversedBy: 'review')]
    #[ORM\JoinColumn(name: 'enrollment_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Enrollment $enrollment;

    /** Denormalized for fast queries */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $rating; // 1–5

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Enrollment $enrollment,
        string $userId,
        string $courseId,
        int $rating,
        ?string $comment = null,
    ) {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5.');
        }
        if (!$enrollment->canWriteReview()) {
            throw new \DomainException('User must be enrolled (active or completed) to write a review.');
        }

        $this->enrollment = $enrollment;
        $this->userId     = $userId;
        $this->courseId   = $courseId;
        $this->rating     = $rating;
        $this->comment    = $comment;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function update(int $rating, ?string $comment): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5.');
        }
        $this->rating  = $rating;
        $this->comment = $comment;
    }

    public function publish(): void { $this->isPublished = true; }
    public function unpublish(): void { $this->isPublished = false; }

    public function getId(): string { return $this->id; }
    public function getEnrollment(): Enrollment { return $this->enrollment; }
    public function getUserId(): string { return $this->userId; }
    public function getCourseId(): string { return $this->courseId; }
    public function getRating(): int { return $this->rating; }
    public function getComment(): ?string { return $this->comment; }
    public function isPublished(): bool { return $this->isPublished; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}