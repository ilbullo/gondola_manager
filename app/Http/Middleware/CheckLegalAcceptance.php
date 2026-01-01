<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckLegalAcceptance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $hasAccepted = Auth::user()->legalAcceptances()
                ->where('version', config('legal.current_version'))
                ->exists();

            if (!$hasAccepted && !$request->routeIs('legal.terms')) {
                return redirect()->route('legal.terms');
            }
        }
        return $next($request);
    }
}
