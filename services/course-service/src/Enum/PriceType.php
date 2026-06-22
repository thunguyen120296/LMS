<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Loại giá / mô hình kinh doanh của khóa học.
 *
 * - Free:         Miễn phí hoàn toàn, không cần thanh toán.
 * - Paid:         Mua một lần, truy cập vĩnh viễn.
 * - Subscription: Chỉ truy cập khi đang có gói subscription hợp lệ.
 */
enum PriceType: string
{
    case Free         = 'free';
    case Paid         = 'paid';
    case Subscription = 'subscription';

    // ----------------------------------------------------------------
    // Display
    // ----------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Free         => 'Miễn phí',
            self::Paid         => 'Trả phí một lần',
            self::Subscription => 'Gói đăng ký',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Free         => 'green',
            self::Paid         => 'blue',
            self::Subscription => 'purple',
        };
    }

    // ----------------------------------------------------------------
    // Behavior flags
    // ----------------------------------------------------------------

    /**
     * Cần tạo Order khi enroll không.
     */
    public function requiresPayment(): bool
    {
        return $this === self::Paid;
    }

    /**
     * Cần kiểm tra subscription còn hạn khi truy cập lesson không.
     */
    public function requiresSubscription(): bool
    {
        return $this === self::Subscription;
    }

    /**
     * Enroll ngay không cần thanh toán.
     */
    public function isFreeAccess(): bool
    {
        return $this === self::Free;
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
}