<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use App\Services\LiquidationService;
use App\DataObjects\LiquidationResult;

/**
 * Class LicenseReceiptModal
 *
 * @package App\Livewire\Ui
 *
 * Rappresenta il dettaglio analitico (Ricevuta) per una singola licenza.
 * Questo componente isola la logica di visualizzazione dei calcoli economici finali,
 * permettendo un controllo granulare tra lavori eseguiti, trattenute e saldi netti.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Visualization: Trasforma il DTO LiquidationResult in una vista leggibile,
 * agendo come interfaccia di sola lettura per il riepilogo finanziario.
 * 2. Computed DTO Integrity: Utilizza le Computed Properties per garantire che la
 * liquidazione sia sempre accessibile, implementando una logica di fallback se i dati
 * non vengono passati direttamente.
 * 3. Contextual Isolation: Permette di analizzare i dati di una licenza senza
 * interferire con lo stato globale della matrice in TableSplitter.
 * 4. Business Logic Fallback: Integra LiquidationService per ricalcolare i valori
 * in tempo reale nel caso di modifiche ai parametri esterni (come il costo bancale).
 *
 * FLUSSO DATI:
 * [TableSplitter] -> dispatch('open-license-receipt', data) -> [LicenseReceiptModal] ->
 * Computed Liquidation -> UI Render (PDF-like preview)
 */

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
