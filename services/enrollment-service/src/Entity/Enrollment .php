<?php

declare(strict_types=1);

namespace App\Enrollment\Entity;

use App\Enrollment\Enum\EnrollmentStatus;
use App\Enrollment\Repository\EnrollmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\Table(name: 'enrollments', schema: 'enrollment')]
#[ORM\UniqueConstraint(name: 'uq_enrollment_user_course', columns: ['user_id', 'course_id'])]
#[ORM\Index(columns: ['user_id', 'status'], name: 'idx_enrollment_user_status')]
#[ORM\Index(columns: ['course_id', 'status'], name: 'idx_enrollment_course_status')]
#[ORM\Index(columns: ['enrolled_at'], name: 'idx_enrollment_enrolled_at')]
#[ORM\HasLifecycleCallbacks]
class Enrollment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref to iam.users — no FK */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    /** Cross-service ref to course.courses — no FK */
    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: EnrollmentStatus::class)]
    private EnrollmentStatus $status = EnrollmentStatus::Active;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '0.00'])]
    private string $completionPercent = '0.00';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $enrolledAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    /** Lifetime access = null; time-limited = set expiry */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiredAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'enrollment', targetEntity: LessonProgress::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lessonProgresses;

    #[ORM\OneToOne(mappedBy: 'enrollment', targetEntity: Certificate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Certificate $certificate = null;

    #[ORM\OneToOne(mappedBy: 'enrollment', targetEntity: Review::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Review $review = null;

    public function __construct(string $userId, string $courseId)
    {
        $this->userId           = $userId;
        $this->courseId         = $courseId;
        $this->lessonProgresses = new ArrayCollection();
        $this->enrolledAt       = new \DateTimeImmutable();
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

    // --- Business logic ---

    /**
     * Recalculates completion percent from lesson progress.
     * Call after each LessonProgress update.
     */
    public function recalculateCompletion(int $totalLessons): void
    {
        if ($totalLessons === 0) {
            $this->completionPercent = '0.00';
            return;
        }

        $completed = $this->lessonProgresses
            ->filter(fn(LessonProgress $lp) => $lp->isCompleted())
            ->count();

        $percent = min(100, round(($completed / $totalLessons) * 100, 2));
        $this->completionPercent = number_format($percent, 2, '.', '');

        if ($percent >= 100.0 && $this->completedAt === null) {
            $this->completedAt = new \DateTimeImmutable();
            $this->status      = EnrollmentStatus::Completed;
        }
    }

    public function markAsCompleted(): void
    {
        $this->status        = EnrollmentStatus::Completed;
        $this->completedAt ??= new \DateTimeImmutable();
        $this->completionPercent = '100.00';
    }

    public function refund(): void
    {
        $this->status = EnrollmentStatus::Refunded;
    }

    public function suspend(): void
    {
        $this->status = EnrollmentStatus::Suspended;
    }

    public function reactivate(): void
    {
        if ($this->expiredAt !== null && $this->expiredAt < new \DateTimeImmutable()) {
            throw new \DomainException('Cannot reactivate an expired enrollment without extending expiry.');
        }
        $this->status = EnrollmentStatus::Active;
    }

    public function extendExpiry(\DateTimeImmutable $newExpiry): void
    {
        $this->expiredAt = $newExpiry;
        if ($this->status === EnrollmentStatus::Expired) {
            $this->status = EnrollmentStatus::Active;
        }
    }

    public function isActive(): bool
    {
        if ($this->status !== EnrollmentStatus::Active) {
            return false;
        }
        if ($this->expiredAt !== null && $this->expiredAt < new \DateTimeImmutable()) {
            return false;
        }
        return true;
    }

    public function isCompleted(): bool
    {
        return $this->status === EnrollmentStatus::Completed;
    }

    public function canWriteReview(): bool
    {
        return $this->isActive() || $this->isCompleted();
    }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getCourseId(): string { return $this->courseId; }
    public function getStatus(): EnrollmentStatus { return $this->status; }
    public function getCompletionPercent(): string { return $this->completionPercent; }
    public function getEnrolledAt(): \DateTimeImmutable { return $this->enrolledAt; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function getExpiredAt(): ?\DateTimeImmutable { return $this->expiredAt; }
    public function setExpiredAt(?\DateTimeImmutable $expiredAt): static { $this->expiredAt = $expiredAt; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getLessonProgresses(): Collection { return $this->lessonProgresses; }
    public function getCertificate(): ?Certificate { return $this->certificate; }
    public function getReview(): ?Review { return $this->review; }
}