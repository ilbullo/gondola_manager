<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class SmallSpinner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('livewire.ui.small-spinner');
    }
}
