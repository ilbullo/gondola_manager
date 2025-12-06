<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon; // Importa Carbon per gestire data e ora

/**
 * Listener per aggiornare il timestamp dell'ultimo accesso dell'utente.
 * 
 * Questo listener viene eseguito quando si verifica l'evento di login.
 */
class UpdateLastLoginAt
{
    /**
     * Gestisce l'evento di login.
     *
     * @param Login $event Evento di login contenente l'utente autenticato
     */
    public function handle(Login $event): void
    {
        // $event->user è l'utente che ha effettuato l'accesso

        // Aggiorna il campo last_login_at con l'ora corrente
        // forceFill bypassa eventuali protezioni di mass assignment
        $event->user->forceFill([
            'last_login_at' => Carbon::now(),
        ])->save(); // Salva immediatamente la modifica nel database
    }
}
