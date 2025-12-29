<?php

namespace App\Enums;

/**
 * Enum UserRole
 *
 * @package App\Enums
 *
 * Definisce i livelli di autorizzazione e i permessi d'accesso all'interno del sistema.
 * Agisce come spina dorsale per la logica di controllo accessi (RBAC - Role-Based Access Control).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Access Control: Centralizza i ruoli validi per le policy di sicurezza e i middleware.
 * 2. Operational Logic: Distingue tra utenti gestionali (Admin), operativi di campo (Bancale)
 * e utenti finali (User), permettendo di variare l'esperienza d'uso.
 * 3. Security Safety: Previene l'assegnazione di ruoli inesistenti attraverso l'uso di tipi nativi PHP.
 * 4. UI Consistency: Fornisce nomenclature standardizzate per la gestione dei profili e dei log di sistema.
 *
 * ESEMPIO DI UTILIZZO:
 * // Nel middleware o nelle Policy:
 * if ($user->role === UserRole::ADMIN) { ... }
 * * // Nella vista profilo:
 * <p>Ruolo: {{ $user->role->label() }}</p>
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
