<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class LoadingOverlay extends Component
{
    /**
     * Stato di visibilità dell’overlay di caricamento.
     * true  = overlay visibile
     * false = overlay nascosto
     */
    public bool $isLoading = false;

    // ===================================================================
    // Event Handlers
    // ===================================================================

    /**
     * Gestisce l’evento Livewire "toggleLoading".
     * Consente ad altri componenti di mostrare o nascondere l’overlay.
     *
     * @param bool $state  Nuovo stato dell’overlay
     */
    #[On('toggleLoading')]
    public function toggle(bool $state): void
    {
        $this->isLoading = $state;
    }

    /**
     * Eventi legacy mantenuti per compatibilità con implementazioni precedenti.
     * Permettono l'attivazione/disattivazione tramite eventi distinti.
     */
    #[On('startLoading')]
    public function start(): void
    {
        $this->toggle(true);
    }

    #[On('stopLoading')]
    public function stop(): void
    {
        $this->toggle(false);
    }

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializza lo stato del componente.
     */
    public function mount(): void
    {
        $this->isLoading = false;
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Restituisce la vista Blade dell’overlay di caricamento.
     */
    public function render()
    {
        return view('livewire.ui.loading-overlay');
    }
}
