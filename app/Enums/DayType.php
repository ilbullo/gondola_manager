<?php

namespace App\Enums;

/**
 * Enum DayType
 * 
 * Rappresenta i tipi di giornata lavorativa.
 * 
 * Valori possibili:
 * - FULL: giornata intera
 * - MORNING: solo mattina
 * - AFTERNOON: solo pomeriggio
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
