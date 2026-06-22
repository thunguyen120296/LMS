<?php declare(strict_types=1);

namespace App\Enum;

enum TransactionStatus: string
{
    case Pending   = 'pending';
    case Success   = 'success';
    case Failed    = 'failed';
    case Cancelled = 'cancelled';
    case Refunded  = 'refunded';
}