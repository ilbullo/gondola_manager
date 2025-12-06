<?php

namespace App\Livewire\Component;

use Livewire\Component;
use Livewire\Attributes\On;   

/**
 * Componente Livewire che rappresenta un box informativo per un lavoro (work).
 * Mostra informazioni come tipo di lavoro, agenzia, voucher, importo, slot occupati e stato condiviso.
 */
class WorkInfoBox extends Component
{
    // Tipo di lavoro selezionato
    public string $workType = '';

    // Etichetta descrittiva del lavoro
    public string $label = '';

    // Nome dell'agenzia associata (opzionale)
    public ?string $agencyName = null;

    // Codice voucher associato al lavoro (opzionale)
    public ?string $voucher = null;

    // Importo associato al lavoro
    public float $amount = 0.0;

    // Numero di slot occupati da questo lavoro
    public int $slotsOccupied = 0;

    // Indica se il lavoro è condiviso dal primo slot
    public bool $sharedFromFirst = false;

    // Indica se il lavoro è escluso
    public bool $excluded = false;

    /**
     * Aggiorna le proprietà del componente quando viene selezionato un lavoro dalla sidebar.
     *
     * L'evento 'workSelected' passa un array $data contenente:
     * - value: tipo di lavoro
     * - label: etichetta del lavoro
     * - voucher: codice voucher
     * - agencyName: nome agenzia
     * - amount: importo del lavoro
     * - slotsOccupied: numero di slot occupati
     * - sharedFromFirst: se condiviso dal primo slot
     * - excluded: se escluso
     *
     * @param array $data Dati del lavoro selezionato
     */
    #[On('workSelected')]   
    public function updateFromSidebar(array $data)
    {
        $this->workType = $data['value'] ?? '';
        $this->label = $data['label'] ?? '';
        $this->voucher = $data['voucher'] ?? '';
        $this->agencyName = $data['agencyName'] ?? null;
        $this->amount = $data['amount'] ?? 90; // valore di default 90 se non presente
        $this->slotsOccupied = $data['slotsOccupied'] ?? 1; // default 1 slot
        $this->sharedFromFirst = $data['sharedFromFirst'] ?? false;
        $this->excluded = $data['excluded'] ?? false;
    }

    /**
     * Renderizza il componente Livewire.
     *
     * Calcola la visibilità del box: non viene mostrato se $workType è vuoto o 'clear'.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $isVisible = $this->workType !== '' && $this->workType !== 'clear';
        return view('livewire.component.work-info-box', compact('isVisible'));
    }
}
