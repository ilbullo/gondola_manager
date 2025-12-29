<?php

namespace App\Policies;

use App\Models\WorkAssignment;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class WorkAssignmentPolicy
 *
 * @package App\Policies
 *
 * Disciplina l'autorizzazione per la gestione operativa dei lavori (Assegnazioni).
 * Assicura che la manipolazione della matrice dei carichi di lavoro sia riservata
 * esclusivamente al personale di coordinamento e agli amministratori.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Operational Integrity: Protegge il flusso di inserimento dati orari e finanziari
 * da modifiche non autorizzate durante il turno.
 * 2. Financial Safety: Impedisce che gli importi (amount) e i flag di esclusione
 * possano essere alterati da chi non ha responsabilità di cassa.
 * 3. Matrix Consistency: Garantisce che solo i ruoli Admin e Bancale possano
 * occupare o liberare slot nella tabella operativa.
 *
 * REGOLE DI BUSINESS:
 * - ADMIN/BANCALE: Hanno il controllo granulare su ogni singola cella della matrice.
 * - USER: Può solo subire l'assegnazione dei lavori (tramite visualizzazione passiva
 * della propria licenza), ma non può inserire, modificare o eliminare record.
 */

class WorkAssignmentPolicy
{
    use HandlesAuthorization;

    // ===================================================================
    // Policy per il modello WorkAssignment
    // ===================================================================

    /**
     * Determina se l'utente può visualizzare tutti i lavori.
     * Solo Admin e Bancale hanno accesso.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può visualizzare un singolo lavoro.
     * Solo Admin e Bancale hanno accesso.
     *
     * @param User $user
     * @param WorkAssignment $workAssignment
     * @return bool
     */
    public function view(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può creare un nuovo lavoro.
     * Solo Admin e Bancale possono creare.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può aggiornare un lavoro esistente.
     * Solo Admin e Bancale possono aggiornare.
     *
     * @param User $user
     * @param WorkAssignment $workAssignment
     * @return bool
     */
    public function update(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può eliminare un lavoro.
     * Solo Admin e Bancale possono eliminare.
     *
     * @param User $user
     * @param WorkAssignment $workAssignment
     * @return bool
     */
    public function delete(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può ripristinare un lavoro eliminato.
     * Solo Admin e Bancale possono farlo.
     *
     * @param User $user
     * @param WorkAssignment $workAssignment
     * @return bool
     */
    public function restore(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    /**
     * Determina se l'utente può eliminare definitivamente un lavoro.
     * Solo Admin e Bancale possono farlo.
     *
     * @param User $user
     * @param WorkAssignment $workAssignment
     * @return bool
     */
    public function forceDelete(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }
}
