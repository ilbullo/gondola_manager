<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use App\Services\LiquidationService;

class LicenseReceiptModal extends Component
{
    public bool $showModal = false;
    public array $license = [];
    public float $bancaleCost = 0.0;

    // ===================================================================
    // Actions
    // ===================================================================

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

    #[Computed]
    public function works(): Collection
    {
        // Trasformiamo la mappa dei lavori in una collezione pulita
        return collect($this->license['worksMap'] ?? [])->filter()->values();
    }

    #[Computed]
    public function walletDetail(): array
    {
        $nCount = $this->works->where('value', 'N')->count();
        // Usiamo il valore 90 o quello da config
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);
        $theoreticalTotal = $nCount * $defaultAmount;
        
        // Il wallet attuale della licenza (quello che hanno fisicamente in tasca dai noli)
        $currentWallet = (float) ($this->license['wallet'] ?? 0);
        
        // Differenza da conguagliare
        $diff = $theoreticalTotal - $currentWallet;

        return [
            'difference' => $diff,
            'unit_price' => $defaultAmount,
            // altri dati utili per la view se necessari
        ];
    }

    /**
     * L'unico metodo di calcolo necessario: delega tutto al Service.
     */
    #[Computed]
    public function liquidation(): array
    {
        return LiquidationService::calculate(
            $this->works, 
            $this->walletDetail['difference'], 
            $this->bancaleCost
        );
    }

    public function render()
    {
        return view('livewire.ui.license-receipt-modal');
    }
}