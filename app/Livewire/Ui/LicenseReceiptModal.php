<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class LicenseReceiptModal extends Component
{
    /**
     * Dati completi della licenza selezionata (intera riga della matrice).
     * Contiene:
     * - user
     * - worksMap
     * - wallet
     * - ecc.
     */
    public array $license = [];

    /**
     * Costo del bancale, passato dal componente padre (TableSplitter).
     * Usato nel calcolo del totale finale.
     */
    public float $bancaleCost = 0.0;

    /**
     * Controlla la visibilità della modale.
     */
    public bool $showModal = false;

    // ===================================================================
    // Event Listeners
    // ===================================================================

    /**
     * Apre la modale ricevendo la licenza selezionata
     * e opzionalmente il costo del bancale.
     *
     * @param array $license   I dati della licenza da mostrare in ricevuta
     * @param float $bancaleCost Costo del bancale (default = 0)
     */
    #[On('open-license-receipt')]
    public function openModal(array $license, float $bancaleCost = 0.0): void
    {
        $this->license     = $license;
        $this->bancaleCost = $bancaleCost;
        $this->showModal   = true;
    }

    /**
     * Chiude la modale e resetta i dati della licenza.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset('license');
    }

    // ===================================================================
    // Computed Data Helpers
    // ===================================================================

    /**
     * Restituisce la collection dei lavori associati,
     * filtrando eventuali null o valori inconsistenti.
     */
    public function getWorks()
    {
        return collect($this->license['worksMap'] ?? [])
            ->filter()
            ->values();
    }

    /**
     * Restituisce solo i lavori di Agenzia (value = 'A').
     */
    public function getAgencyWorks()
    {
        return $this->getWorks()->where('value', 'A');
    }

    /**
     * Restituisce solo i lavori Cash (value = 'X').
     */
    public function getCashWorks()
    {
        return $this->getWorks()->where('value', 'X');
    }

    /**
     * Numero di lavori tipo N.
     */
    public function getNCount()
    {
        return $this->getWorks()->where('value', 'N')->count();
    }

    /**
     * Numero di lavori tipo P.
     */
    public function getPCount()
    {
        return $this->getWorks()->where('value', 'P')->count();
    }

    /**
     * Totale contanti:
     * - Somma dei lavori cash
     * - + eventuale addebito da portafoglio/wallet
     */
    public function getCashTotal()
    {
        // Somma lavori X + eventuale differenza calcolata dal portafoglio
        return $this->getCashWorks()->sum('amount') + $this->getWalletAmount() ?? 0;
    }

    /**
     * Calcola la differenza tra (numero lavori N * 90€)
     * e il wallet della licenza.
     *
     * Se wallet è inferiore al valore teorico dei lavori N,
     * la differenza va sommata al totale cash.
     */
    public function getWalletAmount()
    {
        return ($this->getNCount() * config('app_settings.works.default_amount')) - $this->license['wallet'];
    }

    /**
     * Calcolo del totale finale:
     * Totale cash - costo bancale.
     *
     * (La versione con max(0, ...) è stata disattivata
     *  su scelta implementativa.)
     */
    public function getFinalCash()
    {
        return $this->getCashTotal() - $this->bancaleCost;
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Restituisce la vista Blade dedicata alla modale.
     */
    public function render()
    {
        return view('livewire.ui.license-receipt-modal');
    }
}
