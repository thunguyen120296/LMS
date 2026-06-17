<?php

declare(strict_types=1);

namespace App\Course\Entity;

use App\Course\Enum\CourseLevel;
use App\Course\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\Table(name: 'courses', schema: 'course')]
#[ORM\Index(columns: ['slug'], name: 'idx_course_courses_slug')]
#[ORM\Index(columns: ['instructor_id'], name: 'idx_course_courses_instructor')]
#[ORM\Index(columns: ['category_id', 'is_published'], name: 'idx_course_courses_category_published')]
#[ORM\Index(columns: ['is_published', 'deleted_at'], name: 'idx_course_courses_published')]
#[ORM\Index(columns: ['avg_rating'], name: 'idx_course_courses_rating')]
#[ORM\Index(columns: ['total_students'], name: 'idx_course_courses_students')]
#[ORM\HasLifecycleCallbacks]
class Course
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /**
     * Cross-service reference — NO FK constraint intentionally.
     * Resolved via IAM Service API when needed.
     */
    #[ORM\Column(type: 'uuid')]
    private string $instructorId;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Category $category = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 300, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $previewVideoUrl = null;

    #[ORM\Column(type: Types::STRING, length: 10, options: ['default' => 'vi'])]
    private string $language = 'vi';

    #[ORM\Column(type: Types::STRING, length: 20, enumType: CourseLevel::class, options: ['default' => 'all_levels'])]
    private CourseLevel $level = CourseLevel::AllLevels;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $price = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $discountPrice = null;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['default' => 'VND'])]
    private string $currency = 'VND';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isFree = false;

    /**
     * Denormalized — recomputed by background job on lesson changes.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $durationMinutes = 0;

    /**
     * Denormalized — recomputed when lessons are added/removed.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $totalLessons = 0;

    /**
     * Denormalized — updated by Review aggregate events.
     */
    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0.0])]
    private float $avgRating = 0.0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $totalReviews = 0;

    /**
     * Denormalized — updated by Enrollment Service events.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $totalStudents = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Section::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $sections;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseTag::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $courseTags;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseLearningObjective::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $learningObjectives;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseRequirement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $requirements;

    public function __construct()
    {
        $this->sections           = new ArrayCollection();
        $this->courseTags         = new ArrayCollection();
        $this->learningObjectives = new ArrayCollection();
        $this->requirements       = new ArrayCollection();
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

    // --- Business logic ---

    public function publish(): void
    {
        if ($this->totalLessons === 0) {
            throw new \DomainException('Cannot publish a course with no lessons.');
        }

        $this->isPublished = true;
        $this->publishedAt ??= new \DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
    }

    public function softDelete(): void
    {
        $this->deletedAt   = new \DateTimeImmutable();
        $this->isPublished = false;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getEffectivePrice(): string
    {
        if ($this->isFree) {
            return '0.00';
        }

        return $this->discountPrice ?? $this->price;
    }

    public function recomputeStats(): void
    {
        $this->totalLessons    = 0;
        $this->durationMinutes = 0;

        foreach ($this->sections as $section) {
            foreach ($section->getLessons() as $lesson) {
                if ($lesson->isPublished()) {
                    ++$this->totalLessons;
                    $this->durationMinutes += (int) ceil($lesson->getVideoDurationSec() / 60);
                }
            }
        }
    }

    // --- Getters & Setters ---

    public function getId(): string { return $this->id; }

    public function getInstructorId(): string { return $this->instructorId; }
    public function setInstructorId(string $instructorId): static { $this->instructorId = $instructorId; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getShortDescription(): ?string { return $this->shortDescription; }
    public function setShortDescription(?string $shortDescription): static { $this->shortDescription = $shortDescription; return $this; }

    public function getThumbnailUrl(): ?string { return $this->thumbnailUrl; }
    public function setThumbnailUrl(?string $thumbnailUrl): static { $this->thumbnailUrl = $thumbnailUrl; return $this; }

    public function getPreviewVideoUrl(): ?string { return $this->previewVideoUrl; }
    public function setPreviewVideoUrl(?string $previewVideoUrl): static { $this->previewVideoUrl = $previewVideoUrl; return $this; }

    public function getLanguage(): string { return $this->language; }
    public function setLanguage(string $language): static { $this->language = $language; return $this; }

    public function getLevel(): CourseLevel { return $this->level; }
    public function setLevel(CourseLevel $level): static { $this->level = $level; return $this; }

    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }

    public function getDiscountPrice(): ?string { return $this->discountPrice; }
    public function setDiscountPrice(?string $discountPrice): static { $this->discountPrice = $discountPrice; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): static { $this->currency = $currency; return $this; }

    public function isPublished(): bool { return $this->isPublished; }
    public function isFree(): bool { return $this->isFree; }
    public function setIsFree(bool $isFree): static { $this->isFree = $isFree; return $this; }

    public function getDurationMinutes(): int { return $this->durationMinutes; }
    public function setDurationMinutes(int $durationMinutes): static { $this->durationMinutes = $durationMinutes; return $this; }

    public function getTotalLessons(): int { return $this->totalLessons; }
    public function setTotalLessons(int $totalLessons): static { $this->totalLessons = $totalLessons; return $this; }

    public function getAvgRating(): float { return $this->avgRating; }
    public function setAvgRating(float $avgRating): static { $this->avgRating = $avgRating; return $this; }

    public function getTotalReviews(): int { return $this->totalReviews; }
    public function setTotalReviews(int $totalReviews): static { $this->totalReviews = $totalReviews; return $this; }

    public function getTotalStudents(): int { return $this->totalStudents; }
    public function setTotalStudents(int $totalStudents): static { $this->totalStudents = $totalStudents; return $this; }

    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }

    public function getSections(): Collection { return $this->sections; }
    public function getCourseTags(): Collection { return $this->courseTags; }
    public function getLearningObjectives(): Collection { return $this->learningObjectives; }
    public function getRequirements(): Collection { return $this->requirements; }
}