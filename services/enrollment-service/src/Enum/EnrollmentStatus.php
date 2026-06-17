<?php declare(strict_types=1);

namespace App\Enrollment\Enum;

enum EnrollmentStatus: string
{
    case Active    = 'active';
    case Completed = 'completed';
    case Expired   = 'expired';
    case Refunded  = 'refunded';
    case Suspended = 'suspended';
}