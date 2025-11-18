<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class AgencyModal extends Component
{
    public bool $show = false;
    
    /** @var array<int, mixed> */
    public array $agencies = [];


     #[On('toggleAgencyModal')]
     public function toggle(bool $visible, array $agencies = []): void
     {
         $this->show = $visible;
         $this->agencies = $visible ? $agencies : [];
         $this->resetErrorBag();
     }

     public function close(): void
    {
        $this->show = false;
        $this->agencies = [];
    }

    private function resetState(): void
    {
        $this->agencies = [];
    }

    public function mount(): void
    {
        $this->resetState();
    }

    public function render()
    {
        return view('livewire.ui.agency-modal');
    }
}