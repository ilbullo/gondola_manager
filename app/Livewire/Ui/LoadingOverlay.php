<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class LoadingOverlay extends Component
{
    public bool $isLoading = false;

    protected $listeners = [
        'startLoading' => 'showLoading',
        'stopLoading' => 'hideLoading',
    ];

    public function showLoading()
    {
        $this->isLoading = true;
    }

    public function hideLoading()
    {
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.ui.loading-overlay');
    }
}
