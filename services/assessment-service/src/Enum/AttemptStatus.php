<?php

declare(strict_types=1);

namespace App\Assessment\Enum;

enum AttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted  = 'submitted';
    case Graded     = 'graded';
    case Expired    = 'expired';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Submitted, self::Graded, self::Expired => true,
            default => false,
        };
    }
}
