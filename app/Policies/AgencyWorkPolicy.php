<?php

namespace App\Policies;

use App\Models\AgencyWork;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class AgencyWorkPolicy
 *
 * @package App\Policies
 *
 * Gestisce i criteri di autorizzazione per le transazioni consolidate (AgencyWork).
 * Questa policy protegge i dati finanziari storici, garantendo che le righe di
 * fatturazione e i voucher registrati non siano manipolabili dagli utenti standard.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Financial Data Guard: Protegge l'integrità dei record di lavoro che formano
 * la base per i pagamenti e i report delle agenzie.
 * 2. Administrative Lockdown: Restringe l'accesso CRUD esclusivamente ai ruoli
 * gestionali (Admin, Bancale), escludendo i conducenti dalla gestione dei propri crediti.
 * 3. Scope Management: Definisce la visibilità dei dati storici a livello di sistema.
 *
 * REGOLE DI BUSINESS:
 * - Le transazioni di agenzia sono considerate dati "Audit-sensitive": solo il
 * personale amministrativo può correggere importi o eliminare record dopo il consolidamento.
 */

class AgencyWorkPolicy
{
    use HandlesAuthorization;

    // ===================================================================
    // Controlli di autorizzazione per AgencyWork
    // ===================================================================

    /**
     * Determina se un utente può visualizzare qualsiasi AgencyWork.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Solo amministratori o utenti con ruolo BANCALE possono vedere tutti i record
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può visualizzare un singolo AgencyWork.
     *
     * @param User $user
     * @param AgencyWork $agencyWork
     * @return bool
     */
    public function view(User $user, AgencyWork $agencyWork): bool
    {
        // Permessi identici a viewAny
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può creare un nuovo AgencyWork.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può aggiornare un AgencyWork esistente.
     *
     * @param User $user
     * @param AgencyWork $agencyWork
     * @return bool
     */
    public function update(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può eliminare un AgencyWork.
     *
     * @param User $user
     * @param AgencyWork $agencyWork
     * @return bool
     */
    public function delete(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può ripristinare un AgencyWork cancellato.
     *
     * @param User $user
     * @param AgencyWork $agencyWork
     * @return bool
     */
    public function restore(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può eliminare definitivamente un AgencyWork.
     *
     * @param User $user
     * @param AgencyWork $agencyWork
     * @return bool
     */
    public function forceDelete(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }
}
