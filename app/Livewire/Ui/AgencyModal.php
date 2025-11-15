<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class AgencyModal extends Component
{
    public $show = false;
    public $agencies = [];

    protected $listeners = [
        'open-agency-modal' => 'open',
        'close-agency-modal' => 'close',
    ];

    public function open($data = [])
    {
        $this->agencies = $data['agencies'] ?? [];
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->agencies = [];
    }

    public function render()
    {
        return view('livewire.ui.agency-modal');
    }
}