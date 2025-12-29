<?php

namespace App\Enums;

/**
 * Enum WorkType
 *
 * @package App\Enums
 *
 * Definisce le tipologie di servizio gestite dal sistema e i relativi identificativi tecnici.
 * Questa classe è il fulcro della logica di ripartizione, influenzando i calcoli della liquidazione,
 * la generazione dei report agenzie e la tematizzazione dell'interfaccia utente.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Domain Mapping: Lega i codici storici/tecnici (es. 'X', 'A') a concetti di business chiari.
 * 2. Financial Logic: Agisce come discriminante per il calcolo dei compensi nel LiquidationService.
 * 3. Visual Identity: Centralizza il sistema di colori per badge e pulsanti, garantendo che un lavoro
 * di tipo "Agenzia" abbia lo stesso aspetto in ogni parte dell'applicazione.
 * 4. Helper Provider: Offre metodi statici (values, options) per popolare dinamicamente componenti
 * UI come select, filtri o sidebar di assegnazione.
 *
 * ESEMPIO DI UTILIZZO:
 * // Nella Blade per un badge colorato:
 * <span class="{{ $work->type->colourClass() }}">{{ $work->type->label() }}</span>
 * * // Nel Service per filtrare solo i noli:
 * if ($work->type === WorkType::NOLO) { ... }
 */

enum WorkType: string
{
    // Lavoro pagato in contanti
    case CASH = 'X';

    // Lavoro tramite agenzia
    case AGENCY = 'A';

    // Lavoro a noleggio
    case NOLO = 'N';

    // Lavoro a “perdi volta”
    case PERDI_VOLTA = 'P';

    // Lavoro escluso
    case EXCLUDED = 'E';

    // Lavoro fisso
    case FIXED    = 'F';

    /**
     * Restituisce un array con tutti i valori stringa dell'enum.
     *
     * @param array<self> $exclude Casi da escludere
     * @return array<string> Array di valori
     */
    public static function values(array $exclude = []): array
    {
        $cases = array_filter(
            self::cases(),
            fn(self $case) => !in_array($case, $exclude, true)
        );

        return array_map(fn(self $case) => $case->value, $cases);
    }

    /**
     * Restituisce un array associativo [valore => etichetta].
     *
     * @return array<string, string> Array ['X' => 'Contanti', 'A' => 'Agenzia', ...]
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn(self $case) => $case->value, self::cases()),
            array_map(fn(self $case) => $case->label(), self::cases())
        );
    }

    /**
     * Restituisce l'etichetta leggibile per l'utente.
     *
     * Utile per UI, tabelle, dropdown o report.
     *
     * @return string Etichetta del tipo di lavoro
     */
    public function label(): string
    {
        return match ($this) {
            self::CASH        => 'Contanti',
            self::AGENCY      => 'Agenzia',
            self::NOLO        => 'Nolo',
            self::PERDI_VOLTA => 'Perdi Volta',
            self::EXCLUDED    => 'Escluso',
            self::FIXED       => 'Fisso',
        };
    }

    /**
     * Restituisce le classi Tailwind CSS da applicare per il colore del badge o label.
     *
     * Permette di distinguere visivamente i diversi tipi di lavoro nell'interfaccia.
     *
     * @return string Classi CSS (Tailwind)
     */
    public function colourClass(): string
    {
        return match ($this) {
            self::AGENCY      => 'bg-indigo-100 text-indigo-900',
            self::NOLO        => 'bg-yellow-100 text-yellow-900',
            self::CASH        => 'bg-green-100 text-green-900',
            self::PERDI_VOLTA => 'bg-red-100 text-red-900',
            self::EXCLUDED    => 'bg-teal-100 text-teal-900',
            self::FIXED       => 'bg-teal-100 text-teal-900',
            default           => 'bg-gray-100 text-gray-500',
        };
    }

    public function colourButtonsClass(): string
    {
        return match ($this) {
            self::AGENCY      => 'bg-indigo-600',
            self::NOLO        => 'bg-yellow-400',
            self::CASH        => 'bg-emerald-500',
            self::PERDI_VOLTA => 'bg-rose-600 ',
            default           => 'bg-gray-100 text-gray-500',
        };
    }
}
