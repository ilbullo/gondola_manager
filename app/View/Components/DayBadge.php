<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Enums\DayType;

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
