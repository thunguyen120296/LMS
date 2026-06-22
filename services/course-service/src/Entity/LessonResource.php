<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonResourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonResourceRepository::class)]
#[ORM\Table(name: 'lesson_resources', schema: 'course')]
#[ORM\Index(columns: ['lesson_id'], name: 'idx_course_lesson_resources_lesson')]
#[ORM\HasLifecycleCallbacks]
class LessonResource
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Lesson $lesson;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $fileUrl;

    /**
     * e.g. "pdf", "zip", "xlsx", "pptx"
     */
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $fileType = null;

    /**
     * File size in bytes.
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $fileSizeBytes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getFileSizeFormatted(): string
    {
        if ($this->fileSizeBytes === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->fileSizeBytes;
        $unit  = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            ++$unit;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    // --- Getters & Setters ---

    public function getId(): string { return $this->id; }

    public function getLesson(): Lesson { return $this->lesson; }
    public function setLesson(Lesson $lesson): static { $this->lesson = $lesson; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getFileUrl(): string { return $this->fileUrl; }
    public function setFileUrl(string $fileUrl): static { $this->fileUrl = $fileUrl; return $this; }

    public function getFileType(): ?string { return $this->fileType; }
    public function setFileType(?string $fileType): static { $this->fileType = $fileType; return $this; }

    public function getFileSizeBytes(): ?int { return $this->fileSizeBytes; }
    public function setFileSizeBytes(?int $fileSizeBytes): static { $this->fileSizeBytes = $fileSizeBytes; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}