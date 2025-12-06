<?php

namespace App\Policies;

use App\Models\AgencyWork;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

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
