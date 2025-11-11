<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Header extends Component
{
    public $info;
    public $date; 
    public $menuActions;

    public function mount() 
    {
        $this->date = format_date(today());
        $this->info = null;
        $this->menuActions = [
        ['id' => 'savePdfButton', 'label' => __('print works table') . ' (PDF)'],
        ['id' => 'summaryPdfButton', 'label' => __('print agency resume'). ' (PDF)'],
        ['id' => 'officePdfButton', 'label' => __('print office resume'). ' (PDF)']
        ];
    }

    public function render()
    {
        return view('livewire.layout.header');
    }
}
