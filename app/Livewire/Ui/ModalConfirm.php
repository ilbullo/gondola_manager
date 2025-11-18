<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class ModalConfirm extends Component
{
    public bool $show = false;
    public string $message = 'Sei sicuro?';

    public ?string $confirmEvent = null;
    public array|object|string|int|null $confirmPayload = null;

    #[On('openConfirmModal')]
    public function open(array $data = []): void
    {
        // Estrazione dati dall'array con fallback (best practice per compatibilità)
        $this->message = $data['message'] ?? 'Sei sicuro?';
        $this->confirmEvent = $data['confirmEvent'] ?? null;
        $this->confirmPayload = $data['payload'] ?? null;
        $this->show = true;

        $this->resetErrorBag(); // Pulisce errori residui per UX migliore
    }

    public function confirm(): void
    {
        \Log::info('ModalConfirm: confirm() chiamato! Evento: ' . $this->confirmEvent . ', Payload: ', [$this->confirmPayload]);
        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, payload: $this->confirmPayload);
        }

        $this->close();
    }

    public function cancel(): void
    {
        $this->close();
    }

    private function close(): void
    {
        $this->show = false;
        $this->resetState();
    }

    private function resetState(): void
    {
        $this->message = 'Sei sicuro?';
        $this->confirmEvent = null;
        $this->confirmPayload = null;
    }

    public function mount(): void
    {
        $this->resetState();
    }

    public function render()
    {
        return view('livewire.ui.modal-confirm');
    }
}