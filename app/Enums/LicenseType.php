<?php

namespace App\Enums;

/**
 * Enum LicenseType
 *
 * @package App\Enums
 *
 * Questa classe definisce le tipologie di titolarità associate a una licenza nel sistema.
 * Distingue tra l'intestatario effettivo e chi ne esercita le funzioni in sostituzione.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Role Definition: Stabilisce in modo univoco i ruoli legati alla conduzione della licenza.
 * 2. Data Integrity: Impedisce l'inserimento di ruoli non censiti, agendo come vincolo di dominio
 * nel database o nei form di gestione utente.
 * 3. Presentation: Centralizza la traduzione delle chiavi tecniche ('titolare', 'sostituto')
 * in etichette leggibili per l'interfaccia utente (UI).
 * 4. Business Logic: Facilita i controlli di accesso o le logiche di fatturazione basate
 * sulla tipologia di conducente (es. regimi fiscali differenti tra titolari e sostituti).
 *
 * ESEMPIO DI UTILIZZO:
 * $type = LicenseType::from($user->license_type);
 * echo $type->label(); // "Titolare"
 */

enum LicenseType: string
{
    // Licenza del titolare
    case OWNER = 'titolare';

    // Licenza del sostituto
    case SUBSTITUTE = 'sostituto';

    /**
     * Restituisce l'etichetta leggibile per l'utente.
     *
     * @return string Etichetta del tipo di licenza
     */
    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Titolare',
            self::SUBSTITUTE => 'Sostituto',
        };
    }
}
