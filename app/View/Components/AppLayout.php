<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Class AppLayout
 *
 * @package App\View\Components
 *
 * Componente di layout principale del sistema.
 * Definisce la struttura HTML base (Head, Body, Scripts) e funge da contenitore
 * per i componenti della dashboard, gestendo l'iniezione degli asset tramite Vite.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Template Encapsulation: Centralizza le dipendenze esterne (Google Fonts, FontAwesome)
 * e gli script di sistema (Livewire, Alpine.js).
 * 2. Visual Consistency: Garantisce che ogni pagina dell'applicativo mantenga
 * lo stesso schema di navigazione e stile.
 * 3. Slot Management: Gestisce lo slot principale per il contenuto dinamico e
 * l'header per i titoli delle sezioni.
 */

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
