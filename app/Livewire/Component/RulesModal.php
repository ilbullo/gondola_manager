<?php

namespace App\Livewire\Component;

use Livewire\Component;

/**
 * Class RulesModal
 *
 * @package App\Livewire\Component
 *
 * Gestisce lo stato e la logica di visualizzazione della finestra modale dedicata alle regole.
 * Questo componente funge da contenitore informativo (Stateless UI Component) che interagisce
 * con l'utente senza modificare direttamente i dati del database.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Interface Segregation: Isola il contenuto informativo pesante (testi, regole, legende)
 * dalla vista principale, migliorando le performance di rendering della dashboard.
 * 2. State Encapsulation: Gestisce internamente la proprietà booleana $isOpen, fornendo
 * metodi espliciti per la mutazione dello stato.
 * 3. Reusability: Può essere integrato in qualsiasi pagina del sistema semplicemente
 * richiamando il tag livewire, garantendo coerenza visiva.
 *
 * FLUSSO DI INTERAZIONE:
 * - L'utente clicca su un pulsante di aiuto/info.
 * - Il metodo openModal() viene invocato via wire:click.
 * - Il DOM si aggiorna reattivamente mostrando l'overlay del modale.
 */

class RulesModal extends Component
{
    /**
     * Indica se il modale è visibile o nascosto.
     *
     * Viene utilizzata direttamente dal template Blade
     * tramite binding Livewire (es. x-show, wire:model).
     *
     * @var bool
     */
    public bool $isOpen = false;

    // ===================================================================
    // APERTURA E CHIUSURA DEL MODALE
    // ===================================================================

    /**
     * Apre il modale impostando $isOpen a true.
     *
     * Può essere chiamato da bottoni/trigger esterni tramite:
     * wire:click="openModal"
     *
     * @return void
     */
    public function openModal(): void
    {
        $this->isOpen = true;
    }

    /**
     * Chiude il modale impostando $isOpen a false.
     *
     * Può essere richiamato dal pulsante "Chiudi" o clic su overlay.
     *
     * @return void
     */
    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    // ===================================================================
    // RENDER
    // ===================================================================

    /**
     * Renderizza la view del componente.
     *
     * La view contiene il markup del modale e
     * utilizza la proprietà $isOpen per gestire la visibilità.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.component.rules-modal');
    }
}
