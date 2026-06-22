<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ORM\Table(name: 'sections', schema: 'course')]
#[ORM\Index(columns: ['course_id', 'sort_order'], name: 'idx_course_sections_course_order')]
#[ORM\HasLifecycleCallbacks]
class Section
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Course $course;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: Lesson::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $lessons;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
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

    public function getPublishedLessonsCount(): int
    {
        return $this->lessons->filter(fn(Lesson $l) => $l->isPublished())->count();
    }

    public function getTotalDurationSec(): int
    {
        return $this->lessons->reduce(
            fn(int $carry, Lesson $l) => $carry + $l->getVideoDurationSec(),
            0
        );
    }

    // --- Getters & Setters ---

    public function getId(): string { return $this->id; }

    public function getCourse(): Course { return $this->course; }
    public function setCourse(Course $course): static { $this->course = $course; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getLessons(): Collection { return $this->lessons; }
}