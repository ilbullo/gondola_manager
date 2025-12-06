<?php

namespace App\Enums;

/**
 * Enum LicenseType
 * 
 * Rappresenta i tipi di licenza disponibili.
 * 
 * Valori possibili:
 * - OWNER: titolare della licenza
 * - SUBSTITUTE: sostituto della licenza
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
