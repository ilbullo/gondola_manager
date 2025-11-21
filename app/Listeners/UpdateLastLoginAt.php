<?php

namespace App\Listeners;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon; // Importa Carbon per la gestione di data e ora

class UpdateLastLoginAt
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // $event->user contiene l'oggetto dell'utente che ha effettuato l'accesso
        $event->user->forceFill([
            'last_login_at' => Carbon::now(),
        ])->save();
    }
}
