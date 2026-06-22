<?php

declare(strict_types=1);

namespace App\Assessment\Entity;

use App\Assessment\Enum\QuestionType;
use App\Assessment\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'questions', schema: 'assessment')]
#[ORM\Index(columns: ['quiz_id', 'sort_order'], name: 'idx_assessment_questions_quiz_order')]
class Question
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Quiz $quiz;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: QuestionType::class)]
    private QuestionType $type;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '1.00'])]
    private string $points = '1.00';

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    /** @var Collection<int, QuestionOption> */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuestionOption::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $options;

    public function __construct(Quiz $quiz, QuestionType $type, string $content)
    {
        $this->quiz    = $quiz;
        $this->type    = $type;
        $this->content = $content;
        $this->options = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }
    public function getQuiz(): Quiz { return $this->quiz; }
    public function getType(): QuestionType { return $this->type; }
    public function setType(QuestionType $type): static { $this->type = $type; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getExplanation(): ?string { return $this->explanation; }
    public function setExplanation(?string $explanation): static { $this->explanation = $explanation; return $this; }
    public function getPoints(): string { return $this->points; }
    public function setPoints(string $points): static { $this->points = $points; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }

    /** @return Collection<int, QuestionOption> */
    public function getOptions(): Collection { return $this->options; }
}
