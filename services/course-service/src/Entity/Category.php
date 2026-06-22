<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Danh mục khóa học — cấu trúc cây tối đa 2 cấp (root → sub).
 *
 * Ví dụ:
 *   Lập trình (root)
 *   ├── PHP
 *   ├── JavaScript
 *   └── Python
 *   Thiết kế (root)
 *   └── UI/UX
 *
 * Constraint: parent chỉ được là root category (depth = 1).
 * Enforce ở application layer (CategoryService), không phải DB.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories', schema: 'course')]
#[ORM\Index(columns: ['slug'], name: 'idx_course_category_slug')]
#[ORM\Index(columns: ['parent_id', 'is_active', 'sort_order'], name: 'idx_course_category_parent_active')]
#[ORM\Index(columns: ['is_active'], name: 'idx_course_category_active')]
#[ORM\HasLifecycleCallbacks]
class Category
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /**
     * Parent category — null nghĩa là root.
     * Sub-category tham chiếu ngược lên parent.
     */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Category $parent = null;

    /**
     * Danh sách sub-categories — chỉ có ở root.
     *
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $children;

    /**
     * Danh sách khóa học thuộc category này.
     *
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Course::class)]
    private Collection $courses;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $name;

    /**
     * URL-friendly unique identifier.
     * e.g. "lap-trinh", "thiet-ke-do-hoa"
     */
    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $slug;

    /**
     * Mô tả ngắn hiển thị trên trang danh mục.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * URL icon/thumbnail của category (lưu trên CDN).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $iconUrl = null;

    /**
     * Màu đại diện (hex) — dùng cho UI badge/header.
     * e.g. "#3B82F6"
     */
    #[ORM\Column(type: Types::STRING, length: 7, nullable: true)]
    private ?string $color = null;

    /**
     * Thứ tự hiển thị trong danh sách — nhỏ hơn = lên trước.
     */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $sortOrder = 0;

    /**
     * Ẩn/hiện category trên frontend.
     * Admin có thể ẩn tạm khi cần mà không xóa.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    /**
     * Tổng số khóa học đã published — denormalized để tránh COUNT(*) khi render menu.
     * Được cập nhật bởi background job / event listener.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $courseCount = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    // ----------------------------------------------------------------
    // Constructor
    // ----------------------------------------------------------------

    public function __construct(string $name, string $slug, ?Category $parent = null)
    {
        $this->name     = $name;
        $this->slug     = $slug;
        $this->parent   = $parent;
        $this->children = new ArrayCollection();
        $this->courses  = new ArrayCollection();
    }

    // ----------------------------------------------------------------
    // Lifecycle hooks
    // ----------------------------------------------------------------

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

    // ----------------------------------------------------------------
    // Business logic
    // ----------------------------------------------------------------

    /**
     * Category này có phải root không (không có parent).
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * Category này có phải sub-category không.
     */
    public function isSub(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Thêm sub-category vào root.
     * Chỉ cho phép gọi trên root category (depth constraint).
     */
    public function addChild(Category $child): static
    {
        if (!$this->isRoot()) {
            throw new \DomainException(
                sprintf('Category "%s" is already a sub-category and cannot have children.', $this->name)
            );
        }

        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Gỡ sub-category khỏi root (parent → null, trở thành orphan).
     */
    public function removeChild(Category $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * Có sub-categories hay không.
     */
    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    /**
     * Chỉ trả về children đang active.
     *
     * @return Collection<int, Category>
     */
    public function getActiveChildren(): Collection
    {
        return $this->children->filter(fn(Category $c) => $c->isActive());
    }

    /**
     * Tăng course count khi 1 course được publish vào category này.
     * Gọi từ event listener — không gọi trực tiếp trong controller.
     */
    public function incrementCourseCount(): void
    {
        ++$this->courseCount;
    }

    /**
     * Giảm course count khi 1 course bị unpublish/xóa.
     */
    public function decrementCourseCount(): void
    {
        $this->courseCount = max(0, $this->courseCount - 1);
    }

    /**
     * Trả về breadcrumb dạng array từ root đến node hiện tại.
     * e.g. [['id' => '...', 'name' => 'Lập trình', 'slug' => 'lap-trinh'], [...]]
     *
     * @return array<int, array{id: string, name: string, slug: string}>
     */
    public function getBreadcrumb(): array
    {
        $crumbs = [['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug]];

        if ($this->parent !== null) {
            $crumbs = array_merge($this->parent->getBreadcrumb(), $crumbs);
        }

        return $crumbs;
    }

    // ----------------------------------------------------------------
    // Getters & Setters
    // ----------------------------------------------------------------

    public function getId(): string
    {
        return $this->id;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /** @return Collection<int, Category> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /** @return Collection<int, Course> */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function setIconUrl(?string $iconUrl): static
    {
        $this->iconUrl = $iconUrl;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        // Validate hex color format
        if ($color !== null && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new \InvalidArgumentException("Color must be a valid hex code, e.g. '#3B82F6'. Got: '{$color}'");
        }

        $this->color = $color;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = max(0, $sortOrder);
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCourseCount(): int
    {
        return $this->courseCount;
    }

    public function setCourseCount(int $courseCount): static
    {
        $this->courseCount = max(0, $courseCount);
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
