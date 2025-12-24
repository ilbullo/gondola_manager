<?php

namespace App\Livewire\Layout;

use App\Models\Agency;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Enums\WorkType;

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
    public int $slotsOccupied;

    /** Importo predefinito del lavoro */
    public float|int $amount;

    /** Modalità ripartizione attiva? */
    public bool $isRedistributionMode = false;

    /** Stato di apertura della sidebar */
    public bool $sidebarOpen = true;

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializza la sidebar.
     * Accetta una configurazione aggiuntiva per personalizzare la UI.
     */
    public function mount(array $config = []): void
    {
        // Resetta lo stato iniziale
        $this->resetSelection();
    }

    // ===================================================================
    // Azioni UI
    // ===================================================================


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
    #[On('agencySelected')]
    public function selectAgency(int $agencyId): void
    {
        // Usa toBase() o find() ma assicurati di aggiornare anche il tipo
        $agency = Agency::find($agencyId);

        if ($agency) {
            $this->agencyId = $agency->id;
            $this->agencyName = $agency->name;
            
            // Sincronizzazione stato Enum
            $this->applyWorkTypeState(WorkType::AGENCY);

            $this->dispatch('toggleAgencyModal', false);
            $this->emitWorkSelected();
        }
    }

    /**
     * Aggiorna più proprietà insieme (da modal dettagli lavoro).
     */
    #[On('updateWorkDetails')]
    public function updateWorkDetails(array $details): void
    {
        $this->amount          = $details['amount'] ?? config('app_settings.works.default_amount');;
        $this->slotsOccupied   = $details['slotsOccupied'] ?? config('app_settings.works.default_slots');;
        $this->excluded        = $details['excluded'];
        $this->sharedFromFirst = $details['sharedFromFirst'];

        $this->emitWorkSelected();
    }

    // ===================================================================
    // Selezione tipo lavoro
    // ===================================================================

    public function setWorkType(string $value): void
    {
        $this->resetSelection();

        if ($value === 'clear') return;

        // 1. Deleghiamo la validazione e l'assegnazione
        $type = WorkType::tryFrom($value);
        if (!$type) return;

        $this->applyWorkTypeState($type);

        // 2. Deleghiamo la logica di instradamento (Routing della logica)
        $this->resolveWorkTypeAction($type);
    }

    /**
     * Responsabilità: Aggiornare solo lo stato interno del componente
     */
    protected function applyWorkTypeState(WorkType $type): void
    {
        $this->workType = $type->value;
        $this->label    = $type->label();
    }

    /**
     * Responsabilità: Decidere quale flusso attivare in base al tipo
     */
    protected function resolveWorkTypeAction(WorkType $type): void
    {
        match ($type) {
            WorkType::AGENCY => $this->handleAgencySelection(),
            default          => $this->emitWorkSelected(),
        };
    }

    /**
     * Responsabilità: Gestire esclusivamente il reperimento dati per le agenzie
     */
    protected function handleAgencySelection(): void
    {
        //$agencies = Agency::orderBy('name')->toBase()->get(['id', 'name', 'code'])->toArray();
        $this->dispatch('toggleAgencyModal', true);
    }

    // ===================================================================
    // Reazioni automatiche ai cambi di proprietà
    // ===================================================================

    /**
     * Gestisce il toggle del Lavoro Fisso con mutua esclusione.
     */
    public function toggleExcluded(): void
    {
        $this->excluded = !$this->excluded;
        if ($this->excluded) {
            $this->sharedFromFirst = false;
        }
        $this->emitWorkSelected();
    }

    /**
     * Gestisce il toggle del Condiviso con mutua esclusione.
     */
    public function toggleShared(): void
    {
        $this->sharedFromFirst = !$this->sharedFromFirst;
        if ($this->sharedFromFirst) {
            $this->excluded = false;
        }
        $this->emitWorkSelected();
    }

    /**
     * Aggiorna il metodo updated esistente per gestire la mutua esclusione 
     * anche se i dati arrivano da input diretti o altre interazioni.
     */
    public function updated($property, $value): void
    {
        // Mutua esclusione rapida
        if ($property === 'excluded' && $value) $this->sharedFromFirst = false;
        if ($property === 'sharedFromFirst' && $value) $this->excluded = false;

        // Lista esatta delle proprietà che devono scatenare l'aggiornamento in tabella
        $syncRequired = ['voucher', 'sharedFromFirst', 'slotsOccupied', 'excluded', 'amount'];

        if (in_array($property, $syncRequired)) {
            $this->emitWorkSelected();
        }
    }

    // ===================================================================
    // Azioni tabella
    // ===================================================================

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
        $this->amount          = config('app_settings.works.default_amount');
        $this->slotsOccupied   = config('app_settings.works.default_slots');

        // Chiude eventuale modal agenzie aperto
        $this->dispatch('toggleAgencyModal', false);

        //$this->emitWorkSelected();
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
