<?php

namespace App\Enums;

/**
 * Enum UserRole
 * 
 * Rappresenta i ruoli utente all'interno dell'applicazione.
 * 
 * Valori possibili:
 * - ADMIN: amministratore con privilegi completi
 * - BANCALE: ruolo specifico “bancale” (operativo)
 * - USER: utente standard
 */
enum UserRole: string
{
    // Ruolo di amministratore
    case ADMIN = 'admin';

    // Ruolo operativo “bancale”
    case BANCALE = 'bancale';

    // Ruolo utente standard
    case USER = 'user';

    /**
     * Restituisce l'etichetta leggibile per l'utente.
     *
     * Utile per visualizzazioni in UI, tabelle o dropdown.
     *
     * @return string Etichetta del ruolo
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN   => 'Amministratore',
            self::BANCALE => 'Bancale',
            self::USER    => 'Utente',
        };
    }
}
