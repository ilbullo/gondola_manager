<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Format
{
    /**
     * Valuta: 1250.5 -> 1.250,50 €
     */
    public static function currency($value, $symbol = true, $showDecimals = true)
{
    if (is_string($value)) {
        $value = str_replace(['.', ','], ['', '.'], $value);
    }

    $value = is_numeric($value) ? (float) $value : 0.0;
    
    // Decidiamo quanti decimali mostrare
    $precision = $showDecimals ? 2 : 0;
    
    $formatted = number_format($value, $precision, ',', '.');
    return $symbol ? '€ ' . $formatted : $formatted;
}

    /**
     * Numero senza decimali (utile per conteggi noli o pezzi): 1200 -> 1.200
     */
    public static function number(mixed $value): string
    {
        if (!is_numeric($value)) return '0';
        return number_format((float) $value, 0, ',', '.');
    }

    /** Helper per date */
    public static function date($value, $format = 'd/m/Y')
    {
        if (!$value) return '-';
        return self::parseAnyDate($value)->format($format);
    }

    public static function dateTime($value, $format = 'd/m/Y H:i')
    {
        if (!$value) return '-';
        return self::parseAnyDate($value)->format($format);
    }

    /**
     * Helper privato per centralizzare il parsing ed evitare eccezioni
     */
    private static function parseAnyDate($value): \Carbon\Carbon
    {
        try {
            // 1. Se è già Carbon o DateTime
            if ($value instanceof \DateTimeInterface) {
                return \Carbon\Carbon::instance($value);
            }

            // 2. Se è una stringa con formato italiano (presenza di /)
            if (is_string($value) && str_contains($value, '/')) {
                // Se contiene anche i due punti, ha l'orario
                $format = str_contains($value, ':') ? 'd/m/Y H:i' : 'd/m/Y';
                // Se ha anche i secondi
                if (substr_count($value, ':') == 2) $format .= ':s';
                
                return \Carbon\Carbon::createFromFormat($format, $value);
            }

            // 3. Parsing standard (per Y-m-d o altri formati ISO)
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            // Fallback: se tutto fallisce, ritorna "adesso" per non rompere la pagina
            // o logga l'errore e ritorna un oggetto Carbon base
            return \Carbon\Carbon::now();
        }
    }

    /**
     * Data per gli scontrini (formato compatto): 27 Dic 2025
     */
    public static function dateHuman(mixed $date): string
    {
        if (empty($date)) return '-';
        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    /**
     * Testo abbreviato (per voucher o agenzie): "AGENZIA_VIAGGI" -> "AGEN..."
     */
    public static function trim(string $text, int $limit = 10): string
    {
        return Str::limit(strtoupper($text), $limit, '...');
    }
}