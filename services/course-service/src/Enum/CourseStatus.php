<?php

declare(strict_types=1);

namespace App\Enum;

enum CourseStatus: string
{
    case Draft         = 'draft';
    case PendingReview = 'pending_review';
    case Published     = 'published';
    case Rejected      = 'rejected';
    case Archived      = 'archived';

    // ----------------------------------------------------------------
    // Display
    // ----------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Draft         => 'Bản nháp',
            self::PendingReview => 'Chờ duyệt',
            self::Published     => 'Đã xuất bản',
            self::Rejected      => 'Bị từ chối',
            self::Archived      => 'Đã lưu trữ',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft         => 'gray',
            self::PendingReview => 'yellow',
            self::Published     => 'green',
            self::Rejected      => 'red',
            self::Archived      => 'indigo',
        };
    }

    // ----------------------------------------------------------------
    // Transition guards
    // ----------------------------------------------------------------

    /**
     * Instructor có thể submit để review không.
     */
    public function canSubmitForReview(): bool
    {
        return match ($this) {
            self::Draft, self::Rejected => true,
            default                     => false,
        };
    }

    /**
     * Admin có thể approve (publish) không.
     */
    public function canPublish(): bool
    {
        return $this === self::PendingReview;
    }

    /**
     * Admin có thể reject không.
     */
    public function canReject(): bool
    {
        return $this === self::PendingReview;
    }

    /**
     * Instructor/admin có thể unpublish (→ Draft) không.
     */
    public function canUnpublish(): bool
    {
        return $this === self::Published;
    }

    /**
     * Admin có thể archive không.
     */
    public function canArchive(): bool
    {
        return $this === self::Published;
    }

    /**
     * Course có hiển thị công khai trên frontend không.
     */
    public function isVisible(): bool
    {
        return $this === self::Published;
    }

    /**
     * Học viên đã mua có thể truy cập lesson không.
     */
    public function isAccessible(): bool
    {
        return match ($this) {
            self::Published, self::Archived => true,
            default                         => false,
        };
    }

    // ----------------------------------------------------------------
    // Static helpers
    // ----------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }

    /**
     * Trạng thái mà instructor có thể thấy course của mình.
     *
     * @return self[]
     */
    public static function visibleToInstructor(): array
    {
        return self::cases(); // instructor thấy tất cả
    }

    /**
     * Trạng thái hiển thị cho student/public.
     *
     * @return self[]
     */
    public static function visibleToPublic(): array
    {
        return [self::Published];
    }
}