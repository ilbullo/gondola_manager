<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Converti i ruoli passati come stringhe in Enum
        $allowedRoles = array_map(fn($role) => UserRole::from($role), $roles);

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Accesso non autorizzato per il tuo ruolo.');
        }

        return $next($request);
    }
}