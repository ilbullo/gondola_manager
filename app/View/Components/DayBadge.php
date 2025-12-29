<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Enums\DayType;

/**
 * Class DayBadge
 *
 * @package App\View\Components
 *
 * Componente UI per la rappresentazione visiva dei turni di lavoro.
 * Converte lo stato logico del turno (Enum DayType) in un elemento grafico
 * (Badge) con stili differenziati.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. UI Decoupling: Isola la logica di presentazione dei turni dal resto della matrice.
 * 2. Type Safety: Garantisce che solo valori validi dell'Enum DayType siano processati,
 * fornendo un fallback sicuro (FULL) per dati inconsistenti.
 * 3. Consistent Styling: Centralizza le classi CSS (Tailwind) associate ai turni
 * in un unico file Blade dedicato.
 *
 * MAPPING LOGICO:
 * - FULL: Solitamente rappresentato con colori neutri o primari (es. Blu/Grigio).
 * - MORNING: Colori caldi o luminosi (es. Giallo/Ambra).
 * - AFTERNOON: Colori pomeridiani o profondi (es. Indaco/Arancione).
 */

class DayBadge extends Component
{
    /**
     * Tipo di giorno associato al badge.
     *
     * @var DayType
     */
    public DayType $day;

    /**
     * Crea un nuovo componente DayBadge.
     *
     * @param string $day Valore del giorno come stringa (es. 'FULL', 'MORNING', 'AFTERNOON')
     */
    public function __construct(string $day)
    {
        // Converte la stringa in enum DayType; default FULL se invalido
        $this->day = DayType::tryFrom($day) ?? DayType::FULL;
    }

    /**
     * Restituisce la view associata al componente.
     *
     * @return View|Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.day-badge');
    }
}
