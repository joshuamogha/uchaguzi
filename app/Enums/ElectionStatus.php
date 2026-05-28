<?php

namespace App\Enums;

enum ElectionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
