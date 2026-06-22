<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourseLearningObjectiveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * "What you will learn" bullet points displayed on the course landing page.
 */
#[ORM\Entity(repositoryClass: CourseLearningObjectiveRepository::class)]
#[ORM\Table(name: 'course_learning_objectives', schema: 'course')]
#[ORM\Index(columns: ['course_id', 'sort_order'], name: 'idx_course_objectives_order')]
class CourseLearningObjective
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'learningObjectives')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Course $course;

    #[ORM\Column(type: Types::STRING, length: 500)]
    private string $description;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    public function getId(): string { return $this->id; }

    public function getCourse(): Course { return $this->course; }
    public function setCourse(Course $course): static { $this->course = $course; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }
}