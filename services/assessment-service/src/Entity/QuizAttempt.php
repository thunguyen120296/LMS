<?php

declare(strict_types=1);

namespace App\Assessment\Entity;

use App\Assessment\Enum\AttemptStatus;
use App\Assessment\Repository\QuizAttemptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizAttemptRepository::class)]
#[ORM\Table(name: 'quiz_attempts', schema: 'assessment')]
#[ORM\Index(columns: ['quiz_id', 'user_id', 'status'], name: 'idx_assessment_attempts_quiz_user')]
#[ORM\Index(columns: ['user_id', 'started_at'], name: 'idx_assessment_attempts_user_date')]
class QuizAttempt
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Quiz $quiz;

    /** Cross-service ref → iam.users */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    /** Cross-service ref → enrollment.enrollments */
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $enrollmentId = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: AttemptStatus::class)]
    private AttemptStatus $status = AttemptStatus::InProgress;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $score = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isPassed = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $timeSpentSec = null;

    /** @var Collection<int, QuizAnswer> */
    #[ORM\OneToMany(mappedBy: 'attempt', targetEntity: QuizAnswer::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $answers;

    public function __construct(Quiz $quiz, string $userId, ?string $enrollmentId = null)
    {
        $this->quiz         = $quiz;
        $this->userId       = $userId;
        $this->enrollmentId = $enrollmentId;
        $this->startedAt    = new \DateTimeImmutable();
        $this->answers      = new ArrayCollection();
    }

    public function submit(string $score, bool $isPassed): void
    {
        if ($this->status->isFinal()) {
            throw new \DomainException('Attempt is already finalized.');
        }

        $this->status       = AttemptStatus::Submitted;
        $this->score        = $score;
        $this->isPassed     = $isPassed;
        $this->submittedAt  = new \DateTimeImmutable();
        $this->timeSpentSec = $this->submittedAt->getTimestamp() - $this->startedAt->getTimestamp();
    }

    public function markGraded(): void
    {
        $this->status = AttemptStatus::Graded;
    }

    public function expire(): void
    {
        $this->status = AttemptStatus::Expired;
    }

    public function getId(): string { return $this->id; }
    public function getQuiz(): Quiz { return $this->quiz; }
    public function getUserId(): string { return $this->userId; }
    public function getEnrollmentId(): ?string { return $this->enrollmentId; }
    public function getStatus(): AttemptStatus { return $this->status; }
    public function getScore(): ?string { return $this->score; }
    public function isPassed(): ?bool { return $this->isPassed; }
    public function getStartedAt(): \DateTimeImmutable { return $this->startedAt; }
    public function getSubmittedAt(): ?\DateTimeImmutable { return $this->submittedAt; }
    public function getTimeSpentSec(): ?int { return $this->timeSpentSec; }

    /** @return Collection<int, QuizAnswer> */
    public function getAnswers(): Collection { return $this->answers; }
}
