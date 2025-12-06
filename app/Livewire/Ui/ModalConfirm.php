<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class ModalConfirm extends Component
{
    /**
     * Controlla la visibilità della modale di conferma.
     * true  = modale aperta
     * false = modale chiusa
     */
    public bool $show = false;

    /**
     * Messaggio mostrato all’interno della modale.
     */
    public string $message = 'Sei sicuro?';

    /**
     * Nome dell’evento Livewire che verrà emesso quando l’utente conferma.
     */
    public ?string $confirmEvent = null;

    /**
     * Nome dell’evento Livewire che verrà emesso quando l’utente annulla.
     */
    public ?string $cancelEvent = null;

    /**
     * Payload opzionale che verrà passato all’evento di conferma/cancellazione.
     * Accetta vari tipi per massima compatibilità.
     *
     * @var array|object|string|int|null
     */
    public array|object|string|int|null $confirmPayload = null;

    // ===================================================================
    // Event Handlers
    // ===================================================================

    /**
     * Gestisce l’apertura della modale tramite evento Livewire.
     *
     * @param array $data  Dati ricevuti dal chiamante:
     *   - message        (string) Messaggio personalizzato
     *   - confirmEvent   (string) Evento da emettere in caso di conferma
     *   - cancelEvent    (string) Evento da emettere in caso di annullamento
     *   - payload        (mixed)  Dati da passare agli eventi
     */
    #[On('openConfirmModal')]
    public function open(array $data = []): void
    {
        // Estrazione sicura con default sensati
        $this->message        = $data['message'] ?? 'Sei sicuro?';
        $this->confirmEvent   = $data['confirmEvent'] ?? null;
        $this->cancelEvent    = $data['cancelEvent'] ?? null;
        $this->confirmPayload = $data['payload'] ?? null;

        $this->show = true;
        $this->resetErrorBag(); // Rimuove eventuali errori precedenti
    }

    /**
     * Conferma l’azione richiesta dall’utente.
     * Emette l’evento assegnato e chiude la modale.
     */
    public function confirm(): void
    {
        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, payload: $this->confirmPayload);
        }

        // Dopo la conferma, procedo allo stesso flusso della cancellazione
        $this->cancel();
    }

    /**
     * Annulla la richiesta dell’utente.
     * Chiude la modale e emette un eventuale evento di annullamento.
     */
    public function cancel(): void
    {
        $this->close();

        if ($this->cancelEvent) {
            $this->dispatch($this->cancelEvent, $this->confirmPayload);
        }

        // Ripristina la maggior parte dello state ad eccezione di "show"
        $this->resetExcept('show');
    }

    // ===================================================================
    // Internal Helpers
    // ===================================================================

    /**
     * Chiude la modale e ripristina lo stato interno.
     */
    private function close(): void
    {
        $this->show = false;
        $this->resetState();
    }

    /**
     * Ripristina tutte le proprietà allo stato iniziale.
     */
    private function resetState(): void
    {
        $this->message        = 'Sei sicuro?';
        $this->confirmEvent   = null;
        $this->cancelEvent    = null;
        $this->confirmPayload = null;
    }

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializza lo stato del componente al mount.
     */
    public function mount(): void
    {
        $this->resetState();
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Restituisce la vista Blade della modale di conferma.
     */
    public function render()
    {
        return view('livewire.ui.modal-confirm');
    }
}
