<?php

namespace App\Policies;

use App\Models\LicenseTable;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class LicenseTablePolicy
 *
 * @package App\Policies
 *
 * Disciplina l'accesso alla struttura portante della tabella giornaliera (Ordine di Servizio).
 * Impedisce la manipolazione dei turni e dell'ordinamento delle licenze da parte
 * di utenti non autorizzati, garantendo la stabilità della matrice operativa.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Operational Structure Protection: Protegge le entità che definiscono il perimetro
 * lavorativo giornaliero.
 * 2. Sequence Integrity: Assicura che solo i ruoli di coordinamento (Bancale/Admin)
 * possano eseguire lo 'swap' o la modifica dei turni.
 * 3. Scope Restriction: Inibisce agli utenti standard (User) la visualizzazione
 * analitica dei dati delle altre licenze, se non previsto dalla dashboard aggregata.
 *
 * REGOLE DI BUSINESS:
 * - ADMIN/BANCALE: Gestione totale dell'ordine di servizio.
 * - USER: Escluso dalla gestione strutturale. Nota: L'accesso ai propri lavori
 * è solitamente gestito tramite WorkAssignmentPolicy o logiche di ownership.
 */

class LicenseTablePolicy
{
    use HandlesAuthorization;

    // ===================================================================
    // Controlli di autorizzazione per LicenseTable
    // ===================================================================

    /**
     * Determina se un utente può visualizzare qualsiasi LicenseTable.
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
     * Determina se un utente può visualizzare un singolo LicenseTable.
     *
     * @param User $user
     * @param LicenseTable $licenseTable
     * @return bool
     */
    public function view(User $user, LicenseTable $licenseTable): bool
    {
        // Permessi identici a viewAny
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può creare un nuovo LicenseTable.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può aggiornare un LicenseTable esistente.
     *
     * @param User $user
     * @param LicenseTable $licenseTable
     * @return bool
     */
    public function update(User $user, LicenseTable $licenseTable): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può eliminare un LicenseTable.
     *
     * @param User $user
     * @param LicenseTable $licenseTable
     * @return bool
     */
    public function delete(User $user, LicenseTable $licenseTable): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può ripristinare un LicenseTable cancellato.
     *
     * @param User $user
     * @param LicenseTable $licenseTable
     * @return bool
     */
    public function restore(User $user, LicenseTable $licenseTable): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se un utente può eliminare definitivamente un LicenseTable.
     *
     * @param User $user
     * @param LicenseTable $licenseTable
     * @return bool
     */
    public function forceDelete(User $user, LicenseTable $licenseTable): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }
}
