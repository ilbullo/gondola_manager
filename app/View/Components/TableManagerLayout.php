<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Class TableManagerLayout
 *
 * @package App\View\Components
 *
 * Layout dedicato alla gestione operativa della tabella dei lavori.
 * Agisce come wrapper per il componente Livewire 'TableManager', fornendo
 * il supporto necessario per lo scrolling orizzontale e l'interattività avanzata.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Targeted Encapsulation: Isola gli stili specifici della matrice (es. larghezza
 * fissa delle colonne, overlay di caricamento) dal resto del sito.
 * 2. Performance Optimization: Consente di caricare asset pesanti (es. librerie per
 * il Drag & Drop o Tooltips) solo quando l'utente si trova nella gestione turni.
 * 3. Responsive Container: Gestisce il contenitore overflow per permettere la
 * consultazione della matrice anche su schermi non ultra-wide.
 */

class TableManagerLayout extends Component
{

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table-manager-layout');
    }
}
