<?php

declare(strict_types=1);

namespace App\Enum;

enum NotificationStatus: string
{
    case Pending = 'pending';
    case Sent    = 'sent';
    case Failed  = 'failed';
    case Read    = 'read';

    public function isDelivered(): bool
    {
        return match ($this) {
            self::Sent, self::Read => true,
            default => false,
        };
    }
}
