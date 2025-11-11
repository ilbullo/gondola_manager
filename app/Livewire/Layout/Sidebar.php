<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Sidebar extends Component
{
    public $workType;
    public $agencyName;
    public $voucher;
    public $value;

    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}
