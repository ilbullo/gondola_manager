<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // ===================================================================
    // Policy per il modello User
    // ===================================================================

    /**
     * Determina se l'utente può visualizzare tutti gli utenti.
     * Solo gli amministratori possono farlo.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determina se l'utente può visualizzare un singolo utente.
     * Admin può vedere chiunque, un utente può vedere solo se stesso.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN || $user->id === $model->id;
    }

    /**
     * Determina se l'utente può creare un nuovo utente.
     * Solo Admin ha questo permesso.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determina se l'utente può aggiornare un utente esistente.
     * Admin può aggiornare chiunque,
     * un utente può aggiornare solo se stesso,
     * gli altri ruoli (es. BANCALE) non hanno permesso.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN || ($user->role === UserRole::USER && $user->id === $model->id);
    }

    /**
     * Determina se l'utente può eliminare un utente.
     * Solo Admin può farlo.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determina se l'utente può ripristinare un utente eliminato.
     * Solo Admin può farlo.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determina se l'utente può eliminare definitivamente un utente.
     * Solo Admin può farlo.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
