<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class UserPolicy
 *
 * @package App\Policies
 *
 * Gestisce i privilegi di accesso e modifica per l'entità User.
 * Implementa il massimo livello di restrizione del sistema, isolando la gestione
 * delle identità e dei ruoli dal resto delle operazioni quotidiane.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Identity Governance: Assicura che solo gli amministratori di sistema possano
 * alterare la struttura del personale o i privilegi di accesso.
 * 2. Self-Service Limitation: Permette agli utenti standard la visualizzazione e
 * l'aggiornamento limitato del proprio profilo (Self-Update), ma non dei propri ruoli.
 * 3. Security Isolation: Impedisce anche al ruolo 'Bancale' di interferire con
 * l'anagrafica utenti, separando le responsabilità operative da quelle amministrative.
 *
 * REGOLE DI BUSINESS:
 * - ADMIN: Controllo totale su ogni account.
 * - USER: Può visualizzare e aggiornare solo il proprio profilo.
 * - BANCALE: Trattato come un utente standard per quanto riguarda la gestione account,
 * nonostante i suoi poteri estesi sulla matrice dei lavori.
 */

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
        return $user->isAdmin();
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
        return $user->isAdmin() || $user->id === $model->id;
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
        return $user->isAdmin();
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
        return $user->isAdmin() || ($user->isUser() && $user->id === $model->id);
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
        return $user->isAdmin();
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
        return $user->isAdmin();
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
        return $user->isAdmin();
    }
}
