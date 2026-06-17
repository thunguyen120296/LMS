<?php

declare(strict_types=1);

namespace App\Enrollment\Entity;

use App\Enrollment\Repository\LessonProgressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonProgressRepository::class)]
#[ORM\Table(name: 'lesson_progresses', schema: 'enrollment')]
#[ORM\UniqueConstraint(name: 'uq_enrollment_lesson_progress', columns: ['enrollment_id', 'lesson_id'])]
#[ORM\Index(columns: ['enrollment_id', 'is_completed'], name: 'idx_enrollment_progress_completed')]
#[ORM\HasLifecycleCallbacks]
class LessonProgress
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Enrollment::class, inversedBy: 'lessonProgresses')]
    #[ORM\JoinColumn(name: 'enrollment_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Enrollment $enrollment;

    /** Cross-service ref to course.lessons — no FK */
    #[ORM\Column(type: 'uuid')]
    private string $lessonId;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isCompleted = false;

    /** Cumulative watch time in seconds for video lessons */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $watchDurationSec = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastWatchedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct(Enrollment $enrollment, string $lessonId)
    {
        $this->enrollment = $enrollment;
        $this->lessonId   = $lessonId;
    }

    // --- Business logic ---

    /**
     * Records a watch session. Marks lesson complete when threshold is met.
     *
     * @param int $totalDurationSec Total lesson video duration to compute threshold
     */
    public function recordWatch(int $secondsWatched, int $totalDurationSec = 0): void
    {
        $this->watchDurationSec += $secondsWatched;
        $this->lastWatchedAt    = new \DateTimeImmutable();

        // Auto-complete when watched >= 90% of the video
        if (!$this->isCompleted && $totalDurationSec > 0) {
            $threshold = $totalDurationSec * 0.90;
            if ($this->watchDurationSec >= $threshold) {
                $this->markComplete();
            }
        }
    }

    public function markComplete(): void
    {
        if (!$this->isCompleted) {
            $this->isCompleted   = true;
            $this->completedAt   = new \DateTimeImmutable();
            $this->lastWatchedAt = new \DateTimeImmutable();
        }
    }

    public function undoComplete(): void
    {
        $this->isCompleted = false;
        $this->completedAt = null;
    }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getEnrollment(): Enrollment { return $this->enrollment; }
    public function getLessonId(): string { return $this->lessonId; }
    public function isCompleted(): bool { return $this->isCompleted; }
    public function getWatchDurationSec(): int { return $this->watchDurationSec; }
    public function setWatchDurationSec(int $sec): static { $this->watchDurationSec = $sec; return $this; }
    public function getLastWatchedAt(): ?\DateTimeImmutable { return $this->lastWatchedAt; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
}