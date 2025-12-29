<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class Spinner extends Component
{

    public string $text;

    public function mount($text = null) {
        $this->text = $text ?? "Sincronizzazione";
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('livewire.ui.spinner');
    }
}
