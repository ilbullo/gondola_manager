<?php

namespace App\Enums;

enum WorkType: string
{
    case CASH = 'X'; 
    case AGENCY = 'A';    
    case NOLO = 'N';
    case PERDI_VOLTA = 'P';
    case EXCLUDED = 'E';
    case FIXED    = 'F';

    /**
     * Restituisce la label leggibile per l'utente.
     */
    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Contanti',
            self::AGENCY => 'Agenzia',
            self::NOLO => 'Nolo',
            self::PERDI_VOLTA => 'Perdi Volta',
            self::EXCLUDED => 'Escluso',
            self::FIXED => 'Fisso'
        };
    }

    /**
     * Restituisce le classi Tailwind CSS per lo sfondo.
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
}
