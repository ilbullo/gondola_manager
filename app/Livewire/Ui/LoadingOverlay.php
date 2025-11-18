<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class LoadingOverlay extends Component
{
    public bool $isLoading = false;

    #[On('toggleLoading')]
    public function toggle(bool $state): void
    {
        $this->isLoading = $state;
    }

    // Opzionale: fallback per compatibilità con vecchi eventi
    #[On('startLoading')] public function start() { $this->toggle(true); }
    #[On('stopLoading')]  public function stop()  { $this->toggle(false); }

    public function mount(): void
    {
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.ui.loading-overlay');
    }
}