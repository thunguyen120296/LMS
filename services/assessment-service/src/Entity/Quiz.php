<?php

declare(strict_types=1);

namespace App\Assessment\Entity;

use App\Assessment\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quizzes', schema: 'assessment')]
#[ORM\Index(columns: ['course_id', 'is_published'], name: 'idx_assessment_quizzes_course')]
#[ORM\Index(columns: ['lesson_id'], name: 'idx_assessment_quizzes_lesson')]
#[ORM\HasLifecycleCallbacks]
class Quiz
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref → course.courses */
    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    /** Cross-service ref → course.lessons (optional) */
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $lessonId = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '70.00'])]
    private string $passingScore = '70.00';

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $timeLimitMinutes = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxAttempts = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $shuffleQuestions = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Question> */
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $questions;

    public function __construct(string $courseId, string $title)
    {
        $this->courseId  = $courseId;
        $this->title     = $title;
        $this->questions = new ArrayCollection();
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

    public function publish(): void
    {
        if ($this->questions->isEmpty()) {
            throw new \DomainException('Cannot publish a quiz with no questions.');
        }
        $this->isPublished = true;
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
    }

    public function getTotalPoints(): string
    {
        $total = array_reduce(
            $this->questions->toArray(),
            fn(float $carry, Question $q) => $carry + (float) $q->getPoints(),
            0.0
        );

        return number_format($total, 2, '.', '');
    }

    public function getId(): string { return $this->id; }
    public function getCourseId(): string { return $this->courseId; }
    public function getLessonId(): ?string { return $this->lessonId; }
    public function setLessonId(?string $lessonId): static { $this->lessonId = $lessonId; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPassingScore(): string { return $this->passingScore; }
    public function setPassingScore(string $passingScore): static { $this->passingScore = $passingScore; return $this; }
    public function getTimeLimitMinutes(): ?int { return $this->timeLimitMinutes; }
    public function setTimeLimitMinutes(?int $minutes): static { $this->timeLimitMinutes = $minutes; return $this; }
    public function getMaxAttempts(): ?int { return $this->maxAttempts; }
    public function setMaxAttempts(?int $maxAttempts): static { $this->maxAttempts = $maxAttempts; return $this; }
    public function isShuffleQuestions(): bool { return $this->shuffleQuestions; }
    public function setShuffleQuestions(bool $shuffle): static { $this->shuffleQuestions = $shuffle; return $this; }
    public function isPublished(): bool { return $this->isPublished; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, Question> */
    public function getQuestions(): Collection { return $this->questions; }
}
