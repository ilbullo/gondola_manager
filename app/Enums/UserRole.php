<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case BANCALE = 'bancale';
    case USER = 'user';


    /**
     * Restituisce la label leggibile per l'utente.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Amministratore',
            self::BANCALE => 'Bancale',
            self::USER  => 'Utente'
        };
    }
}
