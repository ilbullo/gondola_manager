<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

/**
 * Class CheckRole
 *
 * @package App\Http\Middleware
 *
 * Middleware di autorizzazione basato sui ruoli (RBAC).
 * Filtra l'accesso alle rotte verificando che l'utente autenticato possieda
 * uno dei ruoli specificati nella definizione della rotta stessa.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Perimeter Security: Impedisce l'accesso a intere sezioni dell'app (es. area Admin)
 * ad utenti non autorizzati prima ancora che la richiesta raggiunga i controller.
 * 2. Enum Integration: Sfrutta l'Enum UserRole per validare i permessi, eliminando
 * il rischio di errori tipografici nel controllo delle stringhe.
 * 3. Flexibility: Supporta il controllo multi-ruolo (OR logic), permettendo di
 * definire rotte accessibili a diversi livelli gerarchici contemporaneamente.
 * 4. Graceful Rejection: Gestisce automaticamente il redirect per utenti non loggati
 * e l'abort (HTTP 403) per utenti loggati ma non autorizzati.
 *
 * CONFIGURAZIONE (bootstrap/app.php o Kernel):
 * ->alias(['role' => \App\Http\Middleware\CheckRole::class])
 *
 * ESEMPIO DI UTILIZZO NELLE ROTTE:
 * Route::middleware('role:admin,bancale')->group(function () { ... });
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
