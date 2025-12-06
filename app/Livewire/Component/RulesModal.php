<?php

namespace App\Livewire\Component;

use Livewire\Component;

/**
 * Class RulesModal
 *
 * Componente Livewire semplice e riutilizzabile che gestisce
 * l'apertura e la chiusura di una finestra modale.
 *
 * Il modale viene controllato tramite la proprietà pubblica $isOpen,
 * modificata attraverso i metodi openModal() e closeModal().
 * La view associata contiene layout e contenuto del modale.
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
