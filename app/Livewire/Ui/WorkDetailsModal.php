<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

/**
 * Class WorkDetailsModal
 *
 * @package App\Livewire\Ui
 *
 * Gestore della configurazione granulare dei parametri di un lavoro.
 * Permette la definizione di importi personalizzati, l'estensione degli slot occupati
 * e l'applicazione di logiche di esclusione o condivisione fiscale/operativa.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Targeted Parameterization: Isola la modifica dei parametri tecnici (amount, slots)
 * per evitare di sovraccaricare l'interfaccia principale della Sidebar.
 * 2. Input Validation: Implementa regole rigorose (rules()) per garantire che i dati
 * finanziari e gli indici di occupazione (1-4) siano coerenti prima del salvataggio.
 * 3. Business Rule Enforcement: Gestisce programmaticamente la mutua esclusione
 * tra gli stati 'Escluso' e 'Condiviso' tramite hook reattivi (updated*).
 * 4. Bi-directional Synchronization: Riceve lo stato attuale tramite 'workSelected'
 * e restituisce le modifiche validate tramite 'updateWorkDetails'.
 *
 * LOGICA DI BUSINESS:
 * - SlotsOccupied: Determina quante celle consecutive verranno bloccate in tabella.
 * - Excluded/Shared: Definisce come il LiquidationService dovrà trattare il lavoro
 * nel calcolo dei totali e delle quote bancale.
 */

class WorkDetailsModal extends Component
{
    // === Stato del modale ===
    /**
     * Indica se il modale dei dettagli lavoro è attualmente visibile.
     * @var bool
     */
    public bool $isOpen = false;

    // === Dati del form ===
    /**
     * Importo associato al lavoro selezionato.
     * Default comune ai nuovi job.
     * @var float|int
     */
    public float|int $amount;

    /**
     * Numero di slot occupati dal lavoro (1–4).
     * @var int
     */
    public int $slotsOccupied = 1;

    /**
     * Valore/lable del lavoro (es. 'X', 'A', ecc.)
     * @var string
     */
    public string $value = "";

    /**
     * Indica se il lavoro è escluso dall’elaborazione.
     * @var bool
     */
    public bool $excluded = false;

    /**
     * Indica se il lavoro è condiviso dal primo lavoro dello stesso valore.
     * @var bool
     */
    public bool $sharedFromFirst = false;

    public function __construct() {
        $this->amount = config('app_settings.works.default_amount');
    }

    // === Regole di validazione ===
    /**
     * Restituisce le regole di validazione per il form del modale.
     * Livewire 3 raccomanda l'override di rules() per pulizia e consistenza.
     *
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'amount'            => 'required|numeric|min:0',
            'slotsOccupied'     => 'required|integer|in:1,2,3,4',
            'excluded'          => 'boolean',
            'sharedFromFirst'   => 'boolean'
        ];
    }

    // === Listener (Livewire 3+) ===

    /**
     * Apre il modale quando riceve l'evento 'openWorkDetailsModal'.
     * Viene spesso usato quando si apre la scheda di un lavoro esistente.
     */
    #[On('openWorkDetailsModal')]
    public function openModal(): void
    {
        $this->isOpen = true;
        $this->resetErrorBag();
    }

    /**
     * Popola i campi del modale con i dati del lavoro selezionato.
     * Eseguito quando il parent emette 'workSelected'.
     *
     * @param array<string, mixed> $work
     */
    #[On('workSelected')]
    public function updateFromSelectedWork(array $work): void
    {
        $this->value            = $work['value'] ?? "X";
        $this->amount           = $work['amount'] ?? config('app_settings.works.default_amount');
        $this->slotsOccupied    = $work['slotsOccupied'] ?? 1;
        $this->excluded         = $work['excluded'] ?? false;
        $this->sharedFromFirst  = $work['sharedFromFirst'] ?? false;
    }

    // === Azioni ===

    /**
     * Valida i dati del form, emette l’evento di aggiornamento verso il parent
     * e chiude il modale.
     */
    public function save(): void
    {
        $this->validate();

        $this->dispatch('updateWorkDetails', [
            'amount'            => $this->amount,
            'slotsOccupied'     => $this->slotsOccupied,
            'excluded'          => $this->excluded,
            'sharedFromFirst'   => $this->sharedFromFirst
        ]);

        $this->closeModal();
    }

    /**
     * Chiude il modale e ripristina lo stato iniziale del form e degli errori.
     */
    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    // === Utilità interne ===

    /**
     * Ripristina i valori del form allo stato iniziale.
     * Usato sia in mount() che alla chiusura del modale.
     */
    private function resetForm(): void
    {
        $this->amount           = config('app_settings.works.default_amount');
        $this->slotsOccupied    = 1;
        $this->excluded         = false;
        $this->sharedFromFirst  = false;
    }

    // === Reattività: proprietà collegate ===

    /**
     * Garantisce coerenza: se il lavoro è escluso, non può essere condiviso.
     *
     * @param bool $value
     */
    public function updatedExcluded($value)
    {
        if ($value) {
            $this->sharedFromFirst = false;
        }
    }

    /*public function updatedSlotsOccupied($value) {

        $this->excluded = $value > 1 ? true : false;
    }*/

    /**
     * Garantisce coerenza: se il lavoro è condiviso, non può essere escluso.
     *
     * @param bool $value
     */
    public function updatedSharedFromFirst($value)
    {
        if ($value) {
            $this->excluded = false;
        }
    }

    // === Ciclo di vita ===

    /**
     * Resetta il form quando il componente viene inizializzato.
     */
    public function mount(): void
    {
        $this->resetForm();
    }

    // === Render ===

    /**
     * Renderizza la view del modale dei dettagli lavoro.
     */
    public function render()
    {
        return view('livewire.ui.work-details-modal');
    }
}
