<?php

namespace App\Policies;

use App\Models\WorkAssignment;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

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
