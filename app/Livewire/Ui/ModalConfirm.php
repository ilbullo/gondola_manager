<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class ModalConfirm extends Component
{
    public $show = false;
    public $message = "Sei sicuro?";
    public $confirmEvent;
    public $confirmPayload;

    protected $listeners = ['openConfirmModal' => 'open'];

    public function open($data = [])
    {
        $this->message = $data['message'] ?? 'Sei sicuro?';
        $this->confirmEvent = $data['confirmEvent'] ?? null;
        $this->confirmPayload = $data['payload'] ?? null;
        $this->show = true;
    }

    public function confirm()
{
    // dispatch dell'evento finale al componente che ascolta
    if ($this->confirmEvent) {
        $this->dispatch($this->confirmEvent, $this->confirmPayload);
    }
    $this->show = false;
}

    public function cancel()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.ui.modal-confirm');
    }
}
