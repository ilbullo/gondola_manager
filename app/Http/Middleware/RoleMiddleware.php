<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RoleMiddleware
 *
 * Controlla che l'utente autenticato abbia uno dei ruoli richiesti
 * per accedere alla risorsa protetta.
 */
class RoleMiddleware
{
    /**
     * Gestisce la richiesta verificando il ruolo dell'utente.
     *
     * @param  \Illuminate\Http\Request  $request   La richiesta HTTP in arrivo
     * @param  \Closure  $next                     Callback che passa la richiesta allo step successivo
     * @param  mixed ...$roles                     Elenco dei ruoli consentiti (passati tramite la route)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        /**
         * Verifica che il ruolo dell’utente appartenga all’elenco dei ruoli
         * indicati nel middleware.
         *
         * Esempio d’uso:
         * Route::get('/admin', ...)->middleware('role:admin,editor');
         */
        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Accesso non autorizzato.');
        }

        // Se il ruolo è valido, la richiesta procede verso il controller
        return $next($request);
    }
}
