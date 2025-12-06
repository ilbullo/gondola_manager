<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class AgencyModal extends Component
{
    /** 
     * Stato di visibilità del modal.
     * True = visibile, False = nascosto.
     */
    public bool $show = false;
    
    /** 
     * Elenco delle agenzie da mostrare nel modal.
     * Viene popolato dinamicamente quando il modal viene aperto.
     *
     * @var array<int, mixed>
     */
    public array $agencies = [];

    // ===================================================================
    // Event Listeners
    // ===================================================================

    /**
     * Evento Livewire che apre/chiude il modal e imposta l’elenco agenzie.
     *
     * @param bool $visible  Stato del modal (true = apri, false = chiudi)
     * @param array $agencies Elenco delle agenzie da visualizzare
     */
    #[On('toggleAgencyModal')]
    public function toggle(bool $visible, array $agencies = []): void
    {
        $this->show = $visible;

        // Se il modal è visibile, carica le agenzie; altrimenti svuota
        $this->agencies = $visible ? $agencies : [];

        // Reset degli errori di validazione (se presenti)
        $this->resetErrorBag();
    }

    // ===================================================================
    // Public Methods
    // ===================================================================

    /**
     * Chiude il modal manualmente e resetta lo stato interno.
     */
    public function close(): void
    {
        $this->show = false;
        $this->agencies = [];
    }

    // ===================================================================
    // Internal Helpers
    // ===================================================================

    /**
     * Ripristina lo stato iniziale del component.
     * Usato all'avvio.
     */
    private function resetState(): void
    {
        $this->agencies = [];
    }

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Metodo mount eseguito alla creazione del component.
     * Assicura uno stato iniziale pulito.
     */
    public function mount(): void
    {
        $this->resetState();
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Restituisce la vista Blade associata al modal.
     */
    public function render()
    {
        return view('livewire.ui.agency-modal');
    }
}
