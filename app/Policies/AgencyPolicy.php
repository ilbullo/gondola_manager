<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class AgencyPolicy
 *
 * @package App\Policies
 *
 * Gestisce le autorizzazioni per l'accesso e la manipolazione delle entità Agency.
 * Implementa un controllo degli accessi basato sui ruoli (RBAC) per proteggere
 * i dati sensibili delle agenzie partner.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Authorization Logic Centralization: Isola tutte le regole di accesso alle
 * agenzie in un unico punto, evitando di duplicare controlli 'if' nei controller.
 * 2. Role Enforcement: Utilizza l'Enum UserRole per garantire che solo gli Admin
 * e i Bancali possano alterare l'anagrafica.
 * 3. Soft-Delete Protection: Fornisce metodi specifici (restore, forceDelete)
 * per gestire in sicurezza il ciclo di vita dei record eliminati logicamente.
 *
 * REGOLE DI BUSINESS:
 * - ADMIN: Accesso totale (Lettura/Scrittura/Eliminazione).
 * - BANCALE: Accesso totale per permettere la gestione operativa delle anagrafiche.
 * - USER: Nessun accesso alle operazioni CRUD delle agenzie.
 */

class AgencyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Agency $agency): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Agency $agency): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Agency $agency): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Agency $agency): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Agency $agency): bool
    {
        return $user->isAdmin();
    }
}
