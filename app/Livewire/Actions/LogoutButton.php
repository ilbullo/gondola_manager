<?php 

namespace App\Livewire\Actions;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Componente Livewire LogoutButton
 *
 * Gestisce la procedura di logout dell’utente e reindirizza
 * alla pagina di login dopo aver invalidato la sessione.
 */
class LogoutButton extends Component
{
    /**
     * Esegue il logout dell’utente autenticato.
     *
     * - Chiude la sessione utente tramite Auth::logout()
     * - Invalida la sessione corrente per sicurezza
     * - Rigenera il token CSRF per evitare attacchi di session fixation
     * - Reindirizza l’utente verso la pagina di login
     */
    public function logout()
    {
        // Disconnette l'utente
        Auth::logout();

        // Invalida completamente la sessione per impedire riutilizzi malevoli
        session()->invalidate();

        // Rigenera il token CSRF per ulteriore sicurezza
        session()->regenerateToken();

        // Reindirizza alla pagina di login
        return redirect()->route('login');
    }

    /**
     * Renderizza la vista del componente.
     *
     * La vista dovrebbe contenere un pulsante o link che chiama logout()
     */
    public function render()
    {
        return view('livewire.actions.logout-button');
    }
}
