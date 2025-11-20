<?php

namespace App\Livewire\Component;

use Livewire\Component;
use Livewire\Attributes\On;   

class WorkInfoBox extends Component
{
    public string $workType = '';
    public string $label = '';
    public ?string $agencyName = null;
    public ?string $voucher = null;
    public float $amount = 0.0;
    public int $slotsOccupied = 0;
    public bool $sharedFromFirst = false;
    public bool $excluded = false;

    #[On('workSelected')]   
    public function updateFromSidebar(array $data)
    {
        $this->workType = $data['value'] ?? '';
        $this->label = $data['label'] ?? '';
        $this->voucher = $data['voucher'] ?? '';
        $this->agencyName = $data['agencyName'] ?? null;
        $this->amount = $data['amount'] ?? 90;
        $this->slotsOccupied = $data['slotsOccupied'] ?? 1;
        $this->sharedFromFirst = $data['sharedFromFirst'] ?? false;
        $this->excluded = $data['excluded'] ?? false;
    }

    public function render()
    {
        $isVisible = $this->workType !== '' && $this->workType !== 'clear';
        return view('livewire.component.work-info-box', compact('isVisible'));
    }
}