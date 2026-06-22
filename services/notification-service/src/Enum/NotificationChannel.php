<?php

declare(strict_types=1);

namespace App\Enum;

enum NotificationChannel: string
{
    case Email  = 'email';
    case InApp  = 'in_app';
    case Push   = 'push';
}
