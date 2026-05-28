<?php

namespace App\Enums;

enum ContestType: string
{
    case Position = 'position';
    case Community = 'community';
    case Committee = 'committee';

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
