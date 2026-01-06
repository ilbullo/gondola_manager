<?php 

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class PdfViewerModal extends Component
{
    public $isOpen = false;
    public $printData = null;

    #[On('open-print-modal')]
    public function open($data)
    {
        $this->printData = $data;
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->printData = null;
    }

    public function render()
    {
        return view('livewire.ui.pdf-viewer-modal');
    }
}