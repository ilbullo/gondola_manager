<?php

namespace App\Policies;

use App\Models\LicenseTable;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

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
