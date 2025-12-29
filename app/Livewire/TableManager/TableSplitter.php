<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\LiquidationService;
use App\Services\AgencyReportService;
use App\DataObjects\LiquidationResult;
use Carbon\Carbon;
use App\Helpers\Format;

/**
 * Class TableSplitter
 *
 * @package App\Livewire\TableManager
 *
 * Motore di liquidazione e redistribuzione lavori (Matrix Engine).
 * Questo componente gestisce la fase finale del turno, calcolando i conguagli economici,
 * permettendo lo spostamento manuale dei lavori "fuori quota" e generando la reportistica PDF.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Final Liquidation: Integra il LiquidationService per calcolare importi, trattenute bancale
 * e saldi netti per ogni licenza basandosi sul costo operativo inserito.
 * 2. Manual Redistribution: Gestisce il parco "lavori non assegnati" (Unassigned Pool),
 * permettendo all'operatore di bilanciare manualmente la matrice tramite drag-and-drop logico.
 * 3. Data Transformation: Utilizza LicenseResource e MatrixSplitterService per convertire
 * il modello relazionale del DB in una struttura a matrice (N x Slot) ottimizzata per la UI.
 * 4. Reporting Bridge: Centralizza la preparazione dei dati per l'esportazione PDF,
 * aggregando i totali tramite il DTO LiquidationResult.
 *
 * FLUSSO OPERATIVO:
 * Inserimento Costo Bancale -> Esecuzione MatrixSplitter -> Rendering Matrice ->
 * (Opzionale) Spostamento Lavori -> Stampa Report.
 *
 * @property array $matrix Rappresentazione strutturata [License][Slot] dei dati di turno.
 * @property float|null $bancaleCost Costo operativo del servizio da ripartire tra i partecipanti.
 */

class TableSplitter extends Component
{
    /**
     * Costo del bancale, inserito all'apertura del componente.
     */
    public ?float $bancaleCost = 0.0;

    /**
     * Stato del modale iniziale per il costo bancale.
     */
    public bool $showBancaleModal = true;

    /**
     * Matrice delle licenze e dei lavori assegnati.
     */
    public array $matrix = [];

    /**
     * Lavori che non sono ancora stati assegnati a nessuna licenza.
     */
    public array $unassignedWorks = [];

    /**
     * Lavoro attualmente selezionato per l'assegnazione manuale.
     */
    public ?array $selectedWork = null;

    // ======================================================================
    // Lifecycle & Initialization
    // ======================================================================

    public function mount(): void
    {
        $this->showBancaleModal = true;
    }

    /**
     * Conferma il costo del bancale e carica la matrice.
     */
    public function confirmBancaleCost(): void
    {
        $cost = (float) str_replace(',', '.', $this->bancaleCost);

        if ($cost < 0) {
            $this->addError('BancaleCost', 'Il costo non può essere negativo.');
            return;
        }

        $this->bancaleCost = $cost;
        $this->showBancaleModal = false;

        $this->loadMatrix();

        $this->dispatch('notify-success', [
            'message' => "Costo bancale impostato a " . Format::currency($cost, true)
        ]);
    }

    public function closeBancaleModal(): void
    {
        $this->showBancaleModal = false;
        $this->loadMatrix();
    }

    /**
     * Recupera i dati dal DB e li trasforma tramite MatrixSplitterService.
     */
    public function loadMatrix(): void
    {
        $licenses = LicenseTable::with([
            'user:id,license_number',
            'works' => fn ($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code'),
        ])
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();

        // 1. Trasformiamo i dati tramite la Resource
        $licenseTable = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();

        // 2. Risolviamo il Service tramite il Container
        $service = app(\App\Services\MatrixSplitterService::class);

        // 3. Eseguiamo la logica di smistamento
        $service->execute($licenseTable);

        // 4. SOLID & DRY: Popoliamo la matrice usando il metodo di calcolo centralizzato
        $this->matrix = $service->matrix->map(function ($license) {

            // Usiamo il factory method già presente nel componente
            $license['liquidation'] = $this->calculateLiquidation($license);

            // Calcoliamo l'occupazione degli slot
            $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();

            return $license;
        })->toArray();

        $this->unassignedWorks = $service->unassignedWorks->toArray();
        $this->selectedWork = null;
    }

    // ======================================================================
    // Eventi & Redistribuzione
    // ======================================================================

    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {
        $this->loadMatrix();
        $this->dispatch('matrix-updated');
    }

    /**
     * Watcher universale sulle proprietà del componente.
     * Qualsiasi cosa cambi (bancale o lavori), i totali si aggiornano.
     */
    public function updated($propertyName): void
    {

        // Se il valore è stato cancellato (null), lo trattiamo come 0 per i calcoli
        if ($propertyName === 'bancaleCost' && is_null($this->bancaleCost)) {
            $this->bancaleCost = 0.0;
        }

        // Se cambia il bancale o la matrice viene manipolata
        if (in_array($propertyName, ['bancaleCost', 'matrix'])) {
            $this->refreshAllLiquidations();
        }
    }

    /**
     * Centralizza il ricalcolo massivo.
     * SOLID: Unico punto di responsabilità per la sincronizzazione dei dati.
     */
    protected function refreshAllLiquidations(): void
    {
        foreach ($this->matrix as $key => $license) {
            $this->matrix[$key]['liquidation'] = $this->calculateLiquidation($license);
            $this->matrix[$key]['slots_occupied'] = collect($license['worksMap'])->filter()->count();
        }
    }

    /**
     * Factory method per la liquidazione.
     */
    protected function calculateLiquidation(array $license): LiquidationResult
    {
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);

        $nCount = collect($license['worksMap'])->where('value', 'N')->count();
        $walletDiff = ($nCount * $defaultAmount) - (float)($license['wallet'] ?? 0);

        return LiquidationService::calculate(
            $license['worksMap'],
            $walletDiff,
            (float) $this->bancaleCost
        );
    }

    // ======================================================================
    // Gestione Lavori (Assegnazione / Rimozione)
    // ======================================================================

    public function removeWork($licenseKey, $slotIndex): void
    {
        $work = $this->matrix[$licenseKey]['worksMap'][$slotIndex] ?? null;
        if (!$work) return;

        $this->dispatch('openConfirmModal', [
            'message'       => 'Vuoi davvero rimuovere questo lavoro dalla matrice?',
            'confirmEvent'  => 'confirmed-remove',
            'payload'       => [
                'licenseKey' => $licenseKey,
                'slotIndex'  => $slotIndex,
                'work'       => $work,
            ]
        ]);
    }

    #[On('confirmed-remove')]
    public function confirmedRemove(array $payload): void
    {
        $licenseKey = $payload['licenseKey'];
        $slotIndex  = $payload['slotIndex'];

        // 1. Recuperiamo l'oggetto lavoro originale
        $work = $this->matrix[$licenseKey]['worksMap'][$slotIndex];

        // 2. Recuperiamo il numero di licenza di questa riga
        $originalLicense = $this->matrix[$licenseKey]['user']['license_number'] ?? '—';

        // 3. SOLID: Aggiungiamo il metadato "prev_license_number" al lavoro
        // In questo modo, quando lo riassegnerai, il dato sarà già dentro il lavoro
        $work['prev_license_number'] = $originalLicense;

        //4. Assegno il lavoro a unassigned works e lo tolgo alla matrice
        $this->unassignedWorks[] = $work;
        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = null;

        $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);
        //$this->dispatch('matrix-updated');
    }

    public function selectUnassignedWork(int $index): void
    {
        $work = $this->unassignedWorks[$index] ?? null;

        if ($this->selectedWork && $this->areWorksEqual($this->selectedWork, $work)) {
            $this->deselectWork();
            return;
        }

        $this->selectedWork = $work;
        $this->dispatch('work-selected');
    }

    public function deselectWork(): void
    {
        $this->selectedWork = null;
        $this->dispatch('work-deselected');
    }

    public function assignToSlot($licenseKey, $slotIndex): void
    {
        if (!$this->selectedWork) return;

        if (!is_null($this->matrix[$licenseKey]['worksMap'][$slotIndex] ?? null)) {
            $this->dispatch('notify', ['message' => 'Slot già occupato!', 'type' => 'error']);
            return;
        }

        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = $this->selectedWork;
        $this->unassignedWorks = array_filter($this->unassignedWorks, fn ($w) => !$this->areWorksEqual($w, $this->selectedWork));
        $this->selectedWork = null;

        /**
         * Forziamo il ricalcolo.
         * Poiché abbiamo modificato un indice annidato, Livewire potrebbe non sentire
         * il cambiamento per l'hook updated(). Chiamiamo il refresh manualmente.
         */
        $this->refreshAllLiquidations();

        //$this->dispatch('matrix-updated');
        //$this->dispatch('work-deselected');
    }

    private function areWorksEqual(?array $a, ?array $b): bool
    {
        if ($a === null || $b === null) return false;
        return ($a['id'] ?? null) == ($b['id'] ?? null);
    }

    // ======================================================================
    // Esportazione PDF & Reportistica
    // ======================================================================

    /**
     * Genera la sessione per il PDF della tabella di ripartizione.
     * Utilizza il DTO LiquidationResult per uniformare i dati.
     */
    public function printSplitTable(): void
    {
        $liquidations = collect($this->matrix)->pluck('liquidation');
        $totals = LiquidationResult::aggregateTotals($liquidations);

        $matrixData = collect($this->matrix)->map(function($l) {
            // Estraiamo i parametri dal DTO
            $params = $l['liquidation']->toPrintParams();

            // Uniamo i dati identificativi E la mappa dei lavori
            return array_merge($params, [
                'license_number' => $l['user']['license_number'] ?? '—',
                'worksMap'       => $l['worksMap'],
            ]);
        })->values()->toArray();

        Session::flash('pdf_generate', [
            'view' => 'pdf.split-table',
            'data' => [
                'matrix'      => $matrixData,
                'totals'      => $totals,
                'bancaleCost' => $this->bancaleCost,
                'generatedBy' => Auth::user()->name,
                'generatedAt' => now(),
                'date'        => today(),
            ],
            'filename'    => 'ripartizione_' . today()->format('Ymd') . '.pdf',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    // In TableSplitter.php

    public function printAgencyReport(AgencyReportService $service): void
    {
        // SOLID: Delega totale della logica algoritmica al service
        $agencyReport = $service->generate($this->matrix);

        Session::flash('pdf_generate', [
            'view' => 'pdf.agency-report',
            'data' => [
                'agencyReport'  => $agencyReport,
                'generatedBy'   => Auth::user()->name,
                'date'          => Format::date(today()),
                'generatedAt'   => now()
            ],
            'filename' => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    public function render()
    {
        return view('livewire.table-manager.matrix-preview');
    }

}
