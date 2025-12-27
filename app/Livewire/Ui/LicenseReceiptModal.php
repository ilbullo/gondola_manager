<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use App\Services\LiquidationService;
use App\DataObjects\LiquidationResult;

class LicenseReceiptModal extends Component
{
    public bool $showModal = false;
    public array $license = [];
    public float $bancaleCost = 0.0;

    // ===================================================================
    // Actions
    // ===================================================================

    /**
     * Riceve i parametri dal TableSplitter.
     * Ora può accettare sia i dati grezzi che i parametri già processati.
     */
    #[On('open-license-receipt')]
    public function openModal(array $license, float $bancaleCost = 0.0): void
    {
        $this->license = $license;
        $this->bancaleCost = (float) $bancaleCost;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset('license', 'bancaleCost');
        $this->resetErrorBag();
    }

    // ===================================================================
    // Computed Properties
    // ===================================================================

    /**
     * Trasforma la mappa dei lavori in una collezione pulita per il calcolo.
     */
    #[Computed]
    public function works(): Collection
    {
        return collect($this->license['worksMap'] ?? [])->filter()->values();
    }

    /**
     * Calcola la differenza di wallet necessaria per il Service.
     */
    #[Computed]
    private function walletDifference(): float
    {
        $nCount = $this->works->where('value', 'N')->count();
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);
        $theoreticalTotal = $nCount * $defaultAmount;
        
        return $theoreticalTotal - (float) ($this->license['wallet'] ?? 0);
    }

    /**
     * SOLID: Restituisce l'oggetto DTO LiquidationResult.
     * La View utilizzerà i metodi dell'oggetto per formattazione e stampa.
     */
    #[Computed]
    public function liquidation(): LiquidationResult
    {
        if (empty($this->license)) {
            return new LiquidationResult();
        }

        return LiquidationService::calculate(
            $this->works, 
            $this->walletDifference, 
            $this->bancaleCost
        );
    }

    public function render()
    {
        return view('livewire.ui.license-receipt-modal');
    }
}