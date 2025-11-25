<?php

namespace App\Enums;

enum DayType: string
{
    case FULL = 'full';
    case MORNING = 'morning';
    case AFTERNOON = 'afternoon';

    public function label(): string
    {
        return match($this) {
            self::FULL => 'Tutto il giorno',
            self::MORNING => 'Mattina',
            self::AFTERNOON => 'Pomeriggio',
        };
    }
}