<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\View\View;

/**
 * Componente UI generico per la gestione di modali di conferma.
 * Utilizza un sistema ad eventi per comunicare con i componenti chiamanti.
 */
class ModalConfirm extends Component
{
    /** @var bool Visibilità della modale */
    public bool $show = false;

    /** @var string Messaggio descrittivo per l'utente */
    public string $message = 'Sei sicuro?';

    /** @var string|null Evento da scatenare in caso di esito positivo */
    public ?string $confirmEvent = null;

    /** @var string|null Evento da scatenare in caso di annullamento */
    public ?string $cancelEvent = null;

    /** @var mixed Dati contestuali da restituire con l'evento (es. ID, array, object) */
    public mixed $payload = null;

    /**
     * Inizializza e visualizza la modale di conferma.
     * * @param array $data {
     * @var string $message      Messaggio personalizzato.
     * @var string $confirmEvent Nome dell'evento di conferma.
     * @var string $cancelEvent  Nome dell'evento di annullamento (opzionale).
     * @var mixed  $payload      Dati da allegare alla risposta.
     * }
     * @return void
     */
    #[On('openConfirmModal')]
    public function open(array $data): void
    {
        // Reset preventivo per evitare che residui di chiamate precedenti appaiano durante l'animazione
        $this->reset(['message', 'confirmEvent', 'cancelEvent', 'payload']);

        $this->message      = $data['message'] ?? 'Sei sicuro?';
        $this->confirmEvent = $data['confirmEvent'] ?? null;
        $this->cancelEvent  = $data['cancelEvent'] ?? null;
        $this->payload      = $data['payload'] ?? null;

        $this->show = true;
    }

    /**
     * Gestisce l'azione di conferma dell'utente.
     * Emette l'evento configurato includendo il payload e chiude la modale.
     * * @return void
     */
    public function confirm(): void
    {
        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, payload: $this->payload);
        }

        $this->close();
    }

    /**
     * Gestisce l'azione di annullamento dell'utente.
     * Emette l'evento di cancellazione se configurato e chiude la modale.
     * * @return void
     */
    public function cancel(): void
    {
        if ($this->cancelEvent) {
            $this->dispatch($this->cancelEvent, payload: $this->payload);
        }

        $this->close();
    }

    /**
     * Chiude la modale e ripristina lo stato predefinito del componente.
     * Utilizzato internamente per garantire la pulizia dei dati sensibili o temporanei.
     * * @return void
     */
    public function close(): void
    {
        $this->show = false;
        $this->reset(); 
    }

    /**
     * Renderizza il template Blade associato al componente.
     * * @return View
     */
    public function render(): View
    {
        return view('livewire.ui.modal-confirm');
    }
}