<?php

namespace App\Livewire\Layout;

use App\Models\Agency;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    // ===================================================================
    // Stato del lavoro selezionato
    // ===================================================================
    public string $workType = '';
    public string $label = '';
    public string $voucher = '';
    public bool $sharedFromFirst = false;
    public ?string $agencyName = null;
    public ?int $agencyId = null;
    public int $slotsOccupied = 1;
    public int $amount = 90;

    public bool $showActions = false;
    public bool $isRedistributionMode = false;

    // ===================================================================
    // Configurazione UI (può essere sovrascritta dal mount)
    // ===================================================================
    public array $config = [
        'work_types' => [
            ['id' => 'quickNoloButton',       'label' => 'NOLO (N)',      'value' => 'N', 'classes' => 'text-gray-900 bg-yellow-400 hover:bg-yellow-500 focus:ring-yellow-300'],
            ['id' => 'quickContantiButton',  'label' => 'CONTANTI (X)',  'value' => 'X', 'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300'],
            ['id' => 'selectAgencyButton',   'label' => 'AGENZIA',       'value' => 'A', 'classes' => 'text-white bg-sky-600 hover:bg-sky-700 focus:ring-sky-300'],
            ['id' => 'quickPerdiVoltaButton','label' => 'PERDI VOLTA (P)','value' => 'P', 'classes' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-300'],
            ['id' => 'clearSelectionButton', 'label' => 'ANNULLA',       'value' => 'clear', 'classes' => 'text-white bg-pink-600 hover:bg-pink-700 focus:ring-pink-300'],
        ],
        'sections' => [
            'agency_input' => ['enabled' => true, 'label' => 'AGENZIA', 'placeholder' => 'Es: Agenzia Ufficiale'],
            'notes'        => ['enabled' => true, 'label' => 'NOTE/VOUCHER', 'placeholder' => 'Es: Voucher 1234'],
            'slots'        => ['enabled' => true, 'label' => 'CASELLE OCCUPATE', 'options' => [['value' => 1, 'label' => '1 Casella'], ['value' => 2, 'label' => '2 Caselle']]],
            'actions'      => [
                ['id' => 'redistributeButton', 'label' => 'RIPARTISCI',        'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700', 'wire' => 'redistributeWorks'],
                ['id' => 'undoButton',         'label' => 'ANNULLA RIPARTIZIONE', 'classes' => 'text-white bg-orange-500 hover:bg-orange-600', 'wire' => 'backToOriginal', 'hidden' => true],
                ['id' => 'updateButton',       'label' => 'MODIFICA TABELLA',  'classes' => 'text-white bg-indigo-600 hover:bg-indigo-700', 'wire' => 'editTable'],
                ['id' => 'resetButton',        'label' => 'RESET TABELLA',     'classes' => 'text-white bg-red-600 hover:bg-red-700', 'wire' => 'resetTable'],
            ],
        ],
    ];

    // ===================================================================
    // Lifecycle
    // ===================================================================
    public function mount(array $config = []): void
    {
        $this->config = array_merge_recursive($this->config, $config);
        $this->resetSelection();
    }

    // ===================================================================
    // Azioni UI
    // ===================================================================
    public function toggleActions(): void
    {
        $this->showActions = !$this->showActions;
    }

    public function openWorkDetailsModal(): void
    {
        $this->dispatch('openWorkDetailsModal');
    }

    // ===================================================================
    // Listener (Livewire v3)
    // ===================================================================
    #[On('selectAgency')]
    public function selectAgency(int $agencyId): void
    {
        $agency = Agency::find($agencyId);

        if ($agency) {
            $this->agencyId = $agency->id;
            $this->agencyName = $agency->name;
            $this->dispatch('toggleAgencyModal', false);
            $this->emitWorkSelected();
        }
    }

    #[On('updateWorkDetails')]
    public function updateWorkDetails(array $details): void
    {
        $this->amount = $details['amount'] ?? 90;
        $this->slotsOccupied = $details['slotsOccupied'] ?? 1;

        $this->emitWorkSelected();
    }

    // ===================================================================
    // Selezione tipo lavoro
    // ===================================================================
    public function setWorkType(string $value): void
    {
        $this->resetSelection();

        if ($value === 'clear') {
            return;
        }

        $workConfig = collect($this->config['work_types'])->firstWhere('value', $value);

        if (!$workConfig) {
            return;
        }

        $this->workType = $workConfig['value'];
        $this->label = $workConfig['label'];

        if ($value === 'A') {
            $agencies = Agency::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name])
                ->toArray();

            $this->dispatch('toggleAgencyModal', true, $agencies);
        } else {
            $this->emitWorkSelected();
        }
    }

    // ===================================================================
    // Aggiornamento automatico su cambio proprietà
    // ===================================================================
    public function updated($property, $value): void
    {
        $relevant = ['voucher', 'sharedFromFirst', 'slotsOccupied', 'amount'];

        if (str($property)->contains($relevant)) {
            $this->emitWorkSelected();
        }
    }

    // ===================================================================
    // Azioni tabella
    // ===================================================================
    public function resetTable(): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Resettare completamente la tabella del giorno?',
            'confirmEvent' => 'resetLicenses',
        ]);
    }

    public function editTable(): void
    {
        // Se si è in modalità ripartizione, si torna alla assegnazione
        if ($this->isRedistributionMode) {
            $this->dispatch('goToAssignmentTable'); // Nuovo evento per TableManager
            session()->flash('success', 'Tornato alla modalità assegnazione lavori.');
            return;
        }

        // Logica originale per tornare a LicenseManager (richiede conferma)
        $this->dispatch('openConfirmModal', [
            'message'      => 'Vuoi tornare in modalità modifica licenze?',
            'confirmEvent' => 'editLicenses', // Evento per TableManager
        ]);
    }

    // Chiama l'evento in tableManager
    public function redistributeWorks(){
        $this->dispatch('callRedistributeWorks');
    }

    // ===================================================================
    // Metodi privati
    // ===================================================================
    private function resetSelection(): void
    {
        $this->workType = '';
        $this->label = '';
        $this->voucher = '';
        $this->sharedFromFirst = false;
        $this->agencyName = null;
        $this->agencyId = null;
        $this->slotsOccupied = 1;
        $this->amount = 90;

        $this->dispatch('toggleAgencyModal', false);
        $this->emitWorkSelected();
    }

    private function emitWorkSelected(): void
    {
        $this->dispatch('workSelected', [
            'value'           => $this->workType,
            'label'           => $this->label,
            'voucher'         => $this->voucher,
            'sharedFromFirst' => $this->sharedFromFirst,
            'excluded'        => false,
            'agencyName'      => $this->workType === 'A' ? $this->agencyName : null,
            'agencyId'        => $this->workType === 'A' ? $this->agencyId : null,
            'slotsOccupied'   => $this->slotsOccupied,
            'amount'          => $this->amount,
        ]);
    }

    // ===================================================================
    // Render
    // ===================================================================
    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}
