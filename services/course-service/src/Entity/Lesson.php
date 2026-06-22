<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\LessonType;
use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'lessons', schema: 'course')]
#[ORM\Index(columns: ['section_id', 'sort_order'], name: 'idx_course_lessons_section_order')]
#[ORM\Index(columns: ['is_published'], name: 'idx_course_lessons_published')]
#[ORM\Index(columns: ['type'], name: 'idx_course_lessons_type')]
#[ORM\HasLifecycleCallbacks]
class Lesson
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Section::class, inversedBy: 'lessons')]
    #[ORM\JoinColumn(name: 'section_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Section $section;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: LessonType::class, options: ['default' => 'video'])]
    private LessonType $type = LessonType::Video;

    /**
     * Used for text/article type lessons (HTML or Markdown content).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    /**
     * CDN URL for video lessons.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $videoDurationSec = 0;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isFreePreview = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: LessonResource::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $resources;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
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
        $this->isPublished = true;
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
    }

    public function getDurationFormatted(): string
    {
        $minutes = intdiv($this->videoDurationSec, 60);
        $seconds = $this->videoDurationSec % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    // --- Getters & Setters ---

    public function getId(): string { return $this->id; }

    public function getSection(): Section { return $this->section; }
    public function setSection(Section $section): static { $this->section = $section; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getType(): LessonType { return $this->type; }
    public function setType(LessonType $type): static { $this->type = $type; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = $content; return $this; }

    public function getVideoUrl(): ?string { return $this->videoUrl; }
    public function setVideoUrl(?string $videoUrl): static { $this->videoUrl = $videoUrl; return $this; }

    public function getVideoDurationSec(): int { return $this->videoDurationSec; }
    public function setVideoDurationSec(int $videoDurationSec): static { $this->videoDurationSec = $videoDurationSec; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }

    public function isFreePreview(): bool { return $this->isFreePreview; }
    public function setIsFreePreview(bool $isFreePreview): static { $this->isFreePreview = $isFreePreview; return $this; }

    public function isPublished(): bool { return $this->isPublished; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getResources(): Collection { return $this->resources; }
}