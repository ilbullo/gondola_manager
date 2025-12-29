<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

/**
 * Class LoadingOverlay
 *
 * @package App\Livewire\Ui
 *
 * Gestore globale dello stato di attesa dell'interfaccia utente.
 * Questo componente fornisce un feedback visivo (spinner/overlay) durante le
 * operazioni asincrone a lunga durata, agendo come un blocco di sicurezza per l'input.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Global Feedback: Centralizza la visualizzazione dello stato di caricamento,
 * evitando la duplicazione di spinner in ogni singolo componente.
 * 2. Event-Driven UI: Reagisce a trigger standardizzati ('toggleLoading', 'startLoading')
 * permettendo a Service e Controller di segnalare l'inizio di processi pesanti.
 * 3. Input Guard: Se implementato con un backdrop a tutto schermo, impedisce
 * all'utente di cliccare pulsanti multipli durante il salvataggio o la generazione di report.
 * 4. Legacy Support: Mantiene la compatibilità con i vecchi trigger del sistema
 * garantendo una transizione fluida verso la nuova architettura di eventi.
 *
 * UTILIZZO TIPICO:
 * $this->dispatch('startLoading'); // All'inizio di un calcolo nel TableSplitter
 * // ... logica complessa ...
 * $this->dispatch('stopLoading'); // Al termine del processo
 */

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
