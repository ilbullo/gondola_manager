<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Class GuestLayout
 *
 * @package App\View\Components
 *
 * Gestisce il layout per gli utenti non autenticati (Guest).
 * Fornisce una struttura semplificata e focalizzata, tipicamente centrata
 * e priva di menu di navigazione complessi, per massimizzare la chiarezza
 * durante l'accesso al sistema.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Security Isolation: Separa visivamente e tecnicamente le pagine pubbliche
 * da quelle protette della dashboard.
 * 2. Focused UX: Riduce le distrazioni visive per agevolare le operazioni di
 * autenticazione.
 * 3. Asset Management: Carica un set di stili e script ridotto rispetto al
 * layout principale, migliorando i tempi di caricamento della pagina di login.
 */

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
