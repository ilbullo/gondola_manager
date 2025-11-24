<?php

namespace App\Enums;

enum LicenseType: string
{
    case OWNER = 'titolare';
    case SUBSTITUTE = 'sostituto';


     /**
     * Restituisce la label leggibile per l'utente.
     */
    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Titolare',
            self::SUBSTITUTE => 'Sostituto'
        };
    }
}
