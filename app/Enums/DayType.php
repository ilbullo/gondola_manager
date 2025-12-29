<?php

namespace App\Enums;

/**
 * Enum DayType
 * * @package App\Enums
 * * Questa classe definisce i tipi di turno/giornata lavorativa disponibili nel sistema.
 * Implementa la logica di visualizzazione e branding per i diversi stati della licenza.
 * * RESPONSABILITÀ:
 * 1. Domain Definition: Centralizza gli unici stati validi per una giornata (Full, Morning, Afternoon).
 * 2. Translation/UI Labeling: Fornisce etichette descrittive per l'utente finale.
 * 3. Branding & Styling: Gestisce la coerenza cromatica (Tailwind CSS) e la simbologia (Badge)
 * attraverso i diversi componenti dell'applicazione (Tabella, Sidebar, Modali).
 * 4. Safety: Garantisce che nel database o negli scambi di dati vengano salvati solo valori validi,
 * prevenendo errori derivanti da stringhe digitate manualmente.
 * * UTILIZZO NELLE VIEW:
 * $license->turn->label();  // "Tutto il giorno"
 * $license->turn->colour(); // "bg-indigo-100 text-indigo-900"
 */

enum DayType: string
{
    // Giornata intera
    case FULL       = 'full';

    // Solo mattina
    case MORNING    = 'morning';

    // Solo pomeriggio
    case AFTERNOON  = 'afternoon';

    /**
     * Restituisce l'etichetta leggibile per l'utente.
     *
     * @return string Etichetta della giornata
     */
    public function label(): string
    {
        return match($this) {
            self::FULL      => 'Tutto il giorno',
            self::MORNING   => 'Mattina',
            self::AFTERNOON => 'Pomeriggio',
        };
    }

    /**
     * Restituisce un badge breve per la giornata.
     * Utile per UI compatte o tabelle.
     *
     * @return string Badge rappresentativo (1 lettera)
     */
    public function badge(): string
    {
        return match($this) {
            self::FULL      => 'F',
            self::MORNING   => 'M',
            self::AFTERNOON => 'P',
        };
    }

    /**
     * Restituisce le classi CSS da applicare per il colore del badge o label.
     * Permette di distinguere visivamente i diversi tipi di giornata.
     *
     * @return string Classi CSS (TailwindCSS)
     */
    public function colour(): string
    {
        return match($this) {
            self::FULL      => 'bg-indigo-100 text-indigo-900',
            self::MORNING   => 'bg-green-100 text-green-900',
            self::AFTERNOON => 'bg-yellow-100 text-yellow-900',
        };
    }
}
