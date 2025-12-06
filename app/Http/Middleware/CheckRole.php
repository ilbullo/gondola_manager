<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

/**
 * Middleware CheckRole
 *
 * Verifica che l’utente autenticato abbia uno dei ruoli autorizzati
 * per accedere alla rotta richiesta.
 */
class CheckRole
{
    /**
     * Gestisce la richiesta in ingresso verificando il ruolo dell’utente.
     *
     * @param  \Illuminate\Http\Request  $request   La richiesta HTTP in arrivo
     * @param  \Closure  $next                     Funzione che passa la richiesta al middleware successivo
     * @param  mixed ...$roles                     Elenco dei ruoli autorizzati (arriva dalle route)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Recupera l’utente attualmente autenticato
        $user = $request->user();

        // Se non autenticato, reindirizza alla pagina di login
        if (!$user) {
            return redirect()->route('login');
        }

        /**
         * Converte i ruoli passati alla route (come stringhe)
         * nei rispettivi Enum UserRole.
         *
         * Esempio d’uso nella route:
         * Route::get('/admin', ...)->middleware('role:admin,bancale');
         */
        $allowedRoles = array_map(fn($role) => UserRole::from($role), $roles);

        // Verifica se il ruolo dell’utente è tra quelli consentiti
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Accesso non autorizzato per il tuo ruolo.');
        }

        // Tutto ok → passa al prossimo middleware o al controller
        return $next($request);
    }
}
