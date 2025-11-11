<?php

use Carbon\Carbon;

if (!function_exists('format_date')) {
    /**
     * Formatta una data nel formato specificato.
     *
     * @param mixed $date Data da formattare (stringa, Carbon, DateTime, ecc.)
     * @param string $format Formato desiderato (default: 'd/m/Y')
     * @return string|null Data formattata o null se non valida
     */
    function format_date($date, $format = 'd/m/Y')
    {
        try {
            if (!$date) {
                return null;
            }
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}