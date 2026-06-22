<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Ngôn ngữ giảng dạy của khóa học.
 * Dùng mã ISO 639-1 làm value để tương thích với i18n.
 */
enum CourseLanguage: string
{
    case Vietnamese = 'vi';
    case English    = 'en';
    case Japanese   = 'ja';
    case Korean     = 'ko';
    case Chinese    = 'zh';
    case French     = 'fr';
    case German     = 'de';
    case Spanish    = 'es';

    // ----------------------------------------------------------------
    // Display
    // ----------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Vietnamese => 'Tiếng Việt',
            self::English    => 'English',
            self::Japanese   => '日本語',
            self::Korean     => '한국어',
            self::Chinese    => '中文',
            self::French     => 'Français',
            self::German     => 'Deutsch',
            self::Spanish    => 'Español',
        };
    }

    /**
     * Emoji quốc kỳ — dùng cho UI badge.
     */
    public function flag(): string
    {
        return match ($this) {
            self::Vietnamese => '🇻🇳',
            self::English    => '🇺🇸',
            self::Japanese   => '🇯🇵',
            self::Korean     => '🇰🇷',
            self::Chinese    => '🇨🇳',
            self::French     => '🇫🇷',
            self::German     => '🇩🇪',
            self::Spanish    => '🇪🇸',
        };
    }

    /**
     * Label kèm quốc kỳ — dùng cho dropdown.
     */
    public function labelWithFlag(): string
    {
        return $this->flag() . ' ' . $this->label();
    }

    // ----------------------------------------------------------------
    // Static helpers
    // ----------------------------------------------------------------

    /**
     * @return array<string, string>  value => labelWithFlag
     */
    public static function choices(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->labelWithFlag();
        }
        return $result;
    }

    public static function default(): self
    {
        return self::Vietnamese;
    }
}