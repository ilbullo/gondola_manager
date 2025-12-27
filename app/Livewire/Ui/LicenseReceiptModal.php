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
        $this->reset(['license', 'bancaleCost']);
        $this->resetErrorBag();
    }

    /**
     * SOLID: Restituisce l'oggetto DTO LiquidationResult.
     * Se il TableSplitter passa già l'oggetto, lo usiamo, altrimenti lo ricalcoliamo.
     */
    #[Computed]
    public function liquidation(): LiquidationResult
    {
        // Se non c'è una licenza selezionata, restituiamo un DTO vuoto per evitare errori nella View
        if (empty($this->license)) {
            return new LiquidationResult();
        }

        // Se la liquidazione è già presente nell'array license (passata da TableSplitter)
        // Livewire la ricostruirà automaticamente tramite l'interfaccia Wireable
        if (isset($this->license['liquidation']) && $this->license['liquidation'] instanceof LiquidationResult) {
            return $this->license['liquidation'];
        }

        // Fallback: Ricalcolo (utile se il modale venisse usato in contesti isolati)
        return LiquidationService::calculate(
            collect($this->license['worksMap'] ?? [])->filter()->values(), 
            $this->walletDifference, 
            $this->bancaleCost
        );
    }

    /**
     * Calcola la differenza di wallet.
     * Nota: Spostato qui solo come logica di supporto al fallback.
     */
    #[Computed]
    public function walletDifference(): float
    {
        $works = collect($this->license['worksMap'] ?? [])->filter();
        $nCount = $works->where('value', 'N')->count();
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);
        $theoreticalTotal = $nCount * $defaultAmount;
        
        return $theoreticalTotal - (float) ($this->license['wallet'] ?? 0);
    }

    public function render()
    {
        return view('livewire.ui.license-receipt-modal');
    }
}