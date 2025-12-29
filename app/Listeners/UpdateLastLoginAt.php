<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon; // Importa Carbon per gestire data e ora

/**
 * Class UpdateLastLoginAt
 *
 * @package App\Listeners
 *
 * Intercetta l'evento di autenticazione riuscita per registrare l'attività dell'utente.
 * Questo componente è essenziale per il monitoraggio della sicurezza e per fornire
 * statistiche sull'utilizzo della piattaforma.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Single Responsibility: Gestisce esclusivamente la persistenza del timestamp di accesso,
 * mantenendo il controller di login pulito.
 * 2. Non-Intrusive Tracking: Utilizza `forceFill` per aggiornare il timestamp anche se il
 * campo non è presente nell'array `$fillable`, garantendo l'integrità del modello User.
 * 3. Temporal Consistency: Sfrutta la libreria Carbon per assicurare che il timestamp
 * sia registrato nel fuso orario corretto configurato nell'applicazione.
 *
 * FLUSSO DI ESECUZIONE:
 * 1. Laravel scatena l'evento `Illuminate\Auth\Events\Login` dopo un'autenticazione valida.
 * 2. Il dispatcher chiama questo Listener.
 * 3. Il record dell'utente viene aggiornato nel database prima del redirect alla dashboard.
 *
 * NOTA TECNICA:
 * Assicurarsi che la colonna `last_login_at` sia presente nella tabella `users`
 * e sia di tipo timestamp/datetime (nullable).
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
