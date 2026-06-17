<?php declare(strict_types=1);

namespace App\Payment\Enum;

enum PayoutStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
    case Rejected   = 'rejected';
}