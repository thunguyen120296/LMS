<?php declare(strict_types=1);

namespace App\Payment\Enum;

enum DiscountType: string
{
    case Percentage = 'percentage'; // e.g. 20% off
    case Fixed      = 'fixed';      // e.g. 50.000 VND off
}