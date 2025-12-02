<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class LicenseReceiptModal extends Component
{
    public array $license = [];         // Riceve tutta la riga della matrice
    public float $bancaleCost = 0.0;    // Costo bancale (può essere passato dal padre)
    public bool $showModal = false;

    // Riceve i dati dal componente padre (TableSplitter)
    #[On('open-license-receipt')]
    public function openModal(array $license, float $bancaleCost = 0.0): void
    {
        $this->license = $license;
        $this->bancaleCost = $bancaleCost;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset('license');
    }

    // Calcoli utili
    public function getWorks()
    {
        return collect($this->license['worksMap'] ?? [])->filter()->values();
    }

    public function getAgencyWorks()
    {
        return $this->getWorks()->where('value', 'A');
    }

    public function getCashWorks()
    {
        return $this->getWorks()->where('value', 'X');
    }

    public function getNCount()
    {
        return $this->getWorks()->where('value', 'N')->count();
    }

    public function getPCount()
    {
        return $this->getWorks()->where('value', 'P')->count();
    }

    public function getCashTotal()
    {
        return $this->getCashWorks()->sum('amount') + $this->getWalletAmount() ?? 0;
    }

    public function getWalletAmount() {

        return  ($this->getNCount() * 90) - $this->license['wallet'];
    }

    public function getFinalCash()
    {
        //return max(0, $this->getCashTotal() - $this->bancaleCost);
        return $this->getCashTotal() - $this->bancaleCost;
    }

    public function render()
    {
        return view('livewire.ui.license-receipt-modal');
    }
}
