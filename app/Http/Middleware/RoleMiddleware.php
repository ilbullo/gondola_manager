<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleMiddleware
 *
 * @package App\Http\Middleware
 *
 * Middleware di autorizzazione granulare basato sulla comparazione dei ruoli.
 * Funge da filtro di sicurezza per proteggere rotte sensibili, garantendo che
 * solo gli utenti con i permessi necessari possano procedere all'esecuzione della logica di business.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Perimeter Defense: Agisce come barriera d'ingresso per i controller, riducendo la superficie di attacco.
 * 2. Clean Routing: Permette di definire le restrizioni d'accesso direttamente nel file delle rotte,
 * mantenendo i controller puliti da controlli di autorizzazione ripetitivi.
 * 3. Parameterized Security: Sfrutta lo spread operator per gestire dinamicamente configurazioni
 * multi-ruolo senza necessità di middleware multipli.
 *
 * NOTA TECNICA:
 * Questo middleware assume che la proprietà 'role' del modello User restituisca un valore
 * comparabile con le stringhe passate nelle rotte (es. tramite casting a Enum o stringa semplice).
 *
 * ESEMPIO DI UTILIZZO:
 * Route::get('/dashboard-bancale', [BoardController::class, 'index'])
 * ->middleware('role:admin,bancale');
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
