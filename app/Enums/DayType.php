<?php

namespace App\Enums;

enum DayType: string
{
    case FULL       = 'full';
    case MORNING    = 'morning';
    case AFTERNOON  = 'afternoon';

    public function label(): string
    {
        return match($this) {
            self::FULL      => 'Tutto il giorno',
            self::MORNING   => 'Mattina',
            self::AFTERNOON => 'Pomeriggio',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::FULL      => 'F',
            self::MORNING   => 'M',
            self::AFTERNOON => 'P',
        };
    }

    public function colour(): string
    {
        return match($this) {
            self::FULL      => 'bg-indigo-100 text-indigo-900',
            self::MORNING   => 'bg-green-100 text-green-900',
            self::AFTERNOON => 'bg-yellow-100 text-yellow-900',
        };
    }
}