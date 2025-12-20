<?php

namespace App\Livewire\Layout;

use App\Models\Agency;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente Sidebar responsabile della selezione e configurazione
 * dei lavori all’interno della Tabella.
 *
 * Gestisce:
 * - Tipo lavoro selezionato
 * - Dettagli (voucher, importo, caselle occupate, ecc.)
 * - Selezione agenzia
 * - Eventi UI e comunicazione con altri componenti (TableManager)
 */
class Sidebar extends Component
{
    // ===================================================================
    // Stato del lavoro selezionato
    // ===================================================================

    /** Tipo di lavoro selezionato (N, X, A, P, ecc.) */
    public string $workType = '';

    /** Etichetta leggibile del tipo di lavoro */
    public string $label = '';

    /** Codice voucher o note inserite */
    public string $voucher = '';

    /** Indica se condiviso dalla prima colonna */
    public bool $sharedFromFirst = false;

    /** Indica se il lavoro è escluso dal calcolo */
    public bool $excluded = false;

    /** Nome agenzia selezionata (solo se workType = A) */
    public ?string $agencyName = null;

    /** ID agenzia selezionata */
    public ?int $agencyId = null;

    /** Numero di caselle occupate dal lavoro */
    public int $slotsOccupied = 1;

    /** Importo predefinito del lavoro */
    public int $amount = 90;

    /** Mostra/nasconde la sezione delle azioni */
    public bool $showActions = false;

    /** Modalità ripartizione attiva? */
    public bool $isRedistributionMode = false;

    /** Stato di apertura della sidebar */
    public bool $sidebarOpen = true;

    // ===================================================================
    // Configurazione UI (può essere sovrascritta dal mount)
    // ===================================================================

    /**
     * Configurazione completa del pannello:
     * - Tipi di lavoro
     * - Sezioni del form
     * - Pulsanti di azione
     */
    public array $config = [
        'work_types' => [
            ['id' => 'quickNoloButton',       'label' => 'NOLO (N)',       'value' => 'N',     'classes' => 'text-gray-900 bg-yellow-400 hover:bg-yellow-500 focus:ring-orange-400','ring' => 'ring-orange-400 ring-offset-1'],
            ['id' => 'quickContantiButton',   'label' => 'CONTANTI (X)',   'value' => 'X',     'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300','ring' => 'ring-emerald-300 ring-offset-1'],
            ['id' => 'selectAgencyButton',    'label' => 'AGENZIA',        'value' => 'A',     'classes' => 'text-white bg-sky-600 hover:bg-sky-700 focus:ring-sky-300','ring' => 'ring-sky-300 ring-offset-1'],
            ['id' => 'quickPerdiVoltaButton', 'label' => 'PERDI VOLTA (P)', 'value' => 'P',     'classes' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-300','ring' => 'ring-red-300 ring-offset-1'],
            ['id' => 'clearSelectionButton',  'label' => 'ANNULLA',        'value' => 'clear', 'classes' => 'text-white bg-pink-600 hover:bg-pink-700 focus:ring-pink-300','ring' => 'ring-pink-300 ring-offset-1'],
        ],
        'sections' => [
            'agency_input' => ['enabled' => true, 'label' => 'AGENZIA',        'placeholder' => 'Es: Agenzia Ufficiale'],
            'notes'        => ['enabled' => true, 'label' => 'NOTE/VOUCHER',   'placeholder' => 'Es: Voucher 1234'],
            'slots'        => ['enabled' => true, 'label' => 'CASELLE OCCUPATE', 'options' => [
                ['value' => 1, 'label' => '1 Casella'],
                ['value' => 2, 'label' => '2 Caselle'],
            ]],
            'actions'      => [
                ['id' => 'redistributeButton', 'label' => 'RIPARTISCI',            'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700', 'wire' => 'redistributeWorks','ring' => 'ring-emerald-300 ring-offset-1'],
                ['id' => 'undoButton',         'label' => 'ANNULLA RIPARTIZIONE',  'classes' => 'text-white bg-orange-500 hover:bg-orange-600',   'wire' => 'backToOriginal', 'hidden' => true,'ring' => 'ring-orange-300 ring-offset-1'],
                ['id' => 'updateButton',       'label' => 'MODIFICA TABELLA',      'classes' => 'text-white bg-indigo-600 hover:bg-indigo-700',   'wire' => 'editTable','ring' => 'ring-indigo-300 ring-offset-1'],
                ['id' => 'printButton',        'label' => 'STAMPA TABELLA',        'classes' => 'text-white bg-blue-600 hover:bg-blue-700',        'wire' => 'printWorks','ring' => 'ring-blue-300 ring-offset-1'],
                ['id' => 'resetButton',        'label' => 'RESET TABELLA',         'classes' => 'text-white bg-red-600 hover:bg-red-700',          'wire' => 'resetTable','ring' => 'ring-red-300 ring-offset-1'],
            ],
        ],
    ];

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializza la sidebar.
     * Accetta una configurazione aggiuntiva per personalizzare la UI.
     */
    public function mount(array $config = []): void
    {
        // Unisce la config personalizzata con quella di default
        $this->config = array_merge_recursive($this->config, $config);

        // Resetta lo stato iniziale
        $this->resetSelection();
    }

    // ===================================================================
    // Azioni UI
    // ===================================================================

    /**
     * Apre la sidebar.
     */
    public function openSidebar(): void
    {
        $this->sidebarOpen = true;
    }

    /**
     * Chiude la sidebar.
     */
    public function closeSidebar(): void
    {
        $this->sidebarOpen = false;
    }


    /**
     * Mostra o nasconde la sezione delle azioni avanzate.
     */
    public function toggleActions(): void
    {
        $this->showActions = !$this->showActions;
    }

    /**
     * Apre il modal dei dettagli lavoro.
     */
    public function openWorkDetailsModal(): void
    {
        $this->dispatch('openWorkDetailsModal');
    }

    // ===================================================================
    // Listener eventi Livewire
    // ===================================================================

    /**
     * Evento chiamato quando viene selezionata un’agenzia dal modal.
     */
    #[On('selectAgency')]
    public function selectAgency(int $agencyId): void
    {
        $agency = Agency::find($agencyId);

        if ($agency) {
            $this->agencyId = $agency->id;
            $this->agencyName = $agency->name;

            // Chiude il modal agenzie
            $this->dispatch('toggleAgencyModal', false);

            // Aggiorna la UI principale
            $this->emitWorkSelected();
        }
    }

    /**
     * Aggiorna più proprietà insieme (da modal dettagli lavoro).
     */
    #[On('updateWorkDetails')]
    public function updateWorkDetails(array $details): void
    {
        $this->amount          = $details['amount'] ?? 90;
        $this->slotsOccupied   = $details['slotsOccupied'] ?? 1;
        $this->excluded        = $details['excluded'];
        $this->sharedFromFirst = $details['sharedFromFirst'];

        $this->emitWorkSelected();
    }

    // ===================================================================
    // Selezione tipo lavoro
    // ===================================================================

    /**
     * Imposta il tipo di lavoro selezionato.
     * Può aprire automaticamente il modal delle agenzie.
     */
    public function setWorkType(string $value): void
    {
        // Resetta prima tutto
        $this->resetSelection();

        if ($value === 'clear') {
            return; // Nessuna selezione
        }

        // Recupera configurazione del tipo selezionato
        $workConfig = collect($this->config['work_types'])
            ->firstWhere('value', $value);

        if (!$workConfig) {
            return;
        }

        $this->workType = $workConfig['value'];
        $this->label    = $workConfig['label'];

        // Caso AGENZIA → apre modal selezione agenzia
        if ($value === 'A') {
            $agencies = Agency::orderBy('name')
                ->get(['id', 'name','code'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])
                ->toArray();

            $this->dispatch('toggleAgencyModal', true, $agencies);
        } else {
            $this->emitWorkSelected();
        }
    }

    // ===================================================================
    // Reazioni automatiche ai cambi di proprietà
    // ===================================================================

    /**
     * Emit automatico quando cambia una proprietà rilevante.
     */
    public function updated($property, $value): void
    {
        $relevant = ['voucher', 'sharedFromFirst', 'slotsOccupied', 'excluded', 'amount'];

        if (str($property)->contains($relevant)) {
            $this->emitWorkSelected();
        }
    }

    // ===================================================================
    // Azioni tabella
    // ===================================================================

    /**
     * Richiede conferma per resettare completamente la tabella.
     */
    public function resetTable(): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Resettare completamente la tabella del giorno?',
            'confirmEvent' => 'resetLicenses',
        ]);
    }

    /**
     * Gestisce la logica della modalità modifica.
     * Cambia comportamento se si è in modalità ripartizione.
     */
    public function editTable(): void
    {
        if ($this->isRedistributionMode) {
            // Torna alla modalità assegnazione
            $this->dispatch('goToAssignmentTable');
            session()->flash('success', 'Tornato alla modalità assegnazione lavori.');
            return;
        }

        // Modalità modifica licenze (con conferma)
        $this->dispatch('openConfirmModal', [
            'message'      => 'Vuoi tornare in modalità modifica licenze?',
            'confirmEvent' => 'editLicenses',
        ]);
    }

    /**
     * Chiede al TableManager di eseguire la ripartizione.
     */
    public function redistributeWorks()
    {
        $this->dispatch('callRedistributeWorks');
    }

    /**
     * Stampa la tabella lavori.
     */
    public function printWorks()
    {
        $this->dispatch("printWorksTable");
    }

    // ===================================================================
    // Metodi privati
    // ===================================================================

    /**
     * Reset completo della selezione lavoro.
     */
    private function resetSelection(): void
    {
        $this->workType        = '';
        $this->label           = '';
        $this->voucher         = '';
        $this->sharedFromFirst = false;
        $this->agencyName      = null;
        $this->agencyId        = null;
        $this->excluded        = false;
        $this->slotsOccupied   = 1;
        $this->amount          = 90;

        // Chiude eventuale modal agenzie aperto
        $this->dispatch('toggleAgencyModal', false);

        $this->emitWorkSelected();
    }

    /**
     * Invia al componente principale tutti i dati aggiornati del lavoro selezionato.
     */
    private function emitWorkSelected(): void
    {
        $this->dispatch('workSelected', [
            'value'           => $this->workType,
            'label'           => $this->label,
            'voucher'         => $this->voucher,
            'sharedFromFirst' => $this->sharedFromFirst,
            'excluded'        => $this->excluded,
            'agencyName'      => $this->workType === 'A' ? $this->agencyName : null,
            'agencyId'        => $this->workType === 'A' ? $this->agencyId : null,
            'slotsOccupied'   => $this->slotsOccupied,
            'amount'          => $this->amount ?? 0,
        ]);
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Renderizza la view della sidebar.
     */
    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}
