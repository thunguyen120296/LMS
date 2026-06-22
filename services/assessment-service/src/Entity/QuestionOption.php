<?php

declare(strict_types=1);

namespace App\Assessment\Entity;

use App\Assessment\Repository\QuestionOptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionOptionRepository::class)]
#[ORM\Table(name: 'question_options', schema: 'assessment')]
#[ORM\Index(columns: ['question_id', 'sort_order'], name: 'idx_assessment_options_question')]
class QuestionOption
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Question $question;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isCorrect = false;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    public function __construct(Question $question, string $content, bool $isCorrect = false)
    {
        $this->question  = $question;
        $this->content   = $content;
        $this->isCorrect = $isCorrect;
    }

    public function getId(): string { return $this->id; }
    public function getQuestion(): Question { return $this->question; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function isCorrect(): bool { return $this->isCorrect; }
    public function setIsCorrect(bool $isCorrect): static { $this->isCorrect = $isCorrect; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }
}
