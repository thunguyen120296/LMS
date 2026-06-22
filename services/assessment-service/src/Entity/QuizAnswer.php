<?php

declare(strict_types=1);

namespace App\Assessment\Entity;

use App\Assessment\Repository\QuizAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizAnswerRepository::class)]
#[ORM\Table(name: 'quiz_answers', schema: 'assessment')]
#[ORM\UniqueConstraint(name: 'uq_assessment_attempt_question', columns: ['attempt_id', 'question_id'])]
class QuizAnswer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: QuizAttempt::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'attempt_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private QuizAttempt $attempt;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Question $question;

    /** @var list<string>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $selectedOptionIds = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textAnswer = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isCorrect = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $pointsEarned = null;

    public function __construct(QuizAttempt $attempt, Question $question)
    {
        $this->attempt  = $attempt;
        $this->question = $question;
    }

    /** @param list<string> $optionIds */
    public function recordChoice(array $optionIds, bool $isCorrect, string $pointsEarned): void
    {
        $this->selectedOptionIds = $optionIds;
        $this->isCorrect         = $isCorrect;
        $this->pointsEarned      = $pointsEarned;
    }

    public function recordText(string $textAnswer, ?bool $isCorrect = null, ?string $pointsEarned = null): void
    {
        $this->textAnswer   = $textAnswer;
        $this->isCorrect    = $isCorrect;
        $this->pointsEarned = $pointsEarned;
    }

    public function getId(): string { return $this->id; }
    public function getAttempt(): QuizAttempt { return $this->attempt; }
    public function getQuestion(): Question { return $this->question; }

    /** @return list<string>|null */
    public function getSelectedOptionIds(): ?array { return $this->selectedOptionIds; }
    public function getTextAnswer(): ?string { return $this->textAnswer; }
    public function isCorrect(): ?bool { return $this->isCorrect; }
    public function getPointsEarned(): ?string { return $this->pointsEarned; }
}
