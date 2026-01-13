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
use App\DataObjects\MatrixTable;
use App\DataObjects\LicenseRow;
use Carbon\Carbon;
use App\Helpers\Format;

class TableSplitter extends Component
{
    public ?float $bancaleCost = 0.0;
    public bool $showBancaleModal = true;
    
    public string $testScenario = ''; // Scenario di test scelto nel modale

    /**
     * Ora usiamo l'oggetto MatrixTable invece di un array generico.
     */
    public MatrixTable $matrixTable;
    
    public array $unassignedWorks = [];
    public ?array $selectedWork = null;

    public function mount(): void
    {
        $this->showBancaleModal = true;
        // Inizializziamo una tabella vuota per evitare errori di proprietà non inizializzata
        $this->matrixTable = new MatrixTable(collect());
    }

    public function confirmBancaleCost(): void
    {
        $cost = (float) str_replace(',', '.', $this->bancaleCost);

        if ($cost < 0) {
            $this->addError('BancaleCost', 'Il costo non può essere negativo.');
            return;
        }

        // --- AGGIUNTA PER IL TEST ---
        if (!empty($this->testScenario)) {
            app()->instance('bug_temporaneo', $this->testScenario);
        }
        // ----------------------------

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

        $licenseTableRaw = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();
        $service = app(\App\Services\MatrixSplitterService::class);
        $service->execute($licenseTableRaw);

        // Trasformiamo i risultati del service in oggetti LicenseRow
        $rows = $service->matrix->map(function ($l) {
            return new LicenseRow(
                user: $l['user'],
                id: $l['id'],
                target_capacity: $l['target_capacity'] ?? 4,
                only_cash_works: $l['only_cash_works'] ?? false,
                wallet: (float) $l['wallet'],
                worksMap: $l['worksMap']
            );
        });

        $this->matrixTable = new MatrixTable($rows);
        
        // Eseguiamo il ricalcolo iniziale
        $this->matrixTable->refreshAll((float) $this->bancaleCost);

        $this->unassignedWorks = $service->unassignedWorks->toArray();
        $this->selectedWork = null;
    }

    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {
        $this->loadMatrix();
        $this->dispatch('matrix-updated');
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'bancaleCost') {
            if (is_null($this->bancaleCost)) $this->bancaleCost = 0.0;
            
            // Rinfresca tutta la matrice con il nuovo costo
            $this->matrixTable->refreshAll((float) $this->bancaleCost);
        }
    }

    /**
     * Rimosso il vecchio refreshAllLiquidations e calculateLiquidation 
     * perché ora la logica risiede dentro MatrixTable e LicenseRow.
     */

    public function removeWork($licenseKey, $slotIndex): void
    {
        $row = $this->matrixTable->rows->get($licenseKey);
        $work = $row->worksMap[$slotIndex] ?? null;
        
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

        $row = $this->matrixTable->rows->get($licenseKey);
        $work = $row->worksMap[$slotIndex];

        $originalLicense = $row->user['license_number'] ?? '—';
        $work['prev_license_number'] = $originalLicense;

        $this->unassignedWorks[] = $work;
        $row->worksMap[$slotIndex] = null;
        
        // Rinfreschiamo i calcoli della riga modificata
        $row->refresh((float) $this->bancaleCost);

        $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);
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

        $row = $this->matrixTable->rows->get($licenseKey);

        if (!is_null($row->worksMap[$slotIndex] ?? null)) {
            $this->dispatch('notify', ['message' => 'Slot già occupato!', 'type' => 'error']);
            return;
        }

        $row->worksMap[$slotIndex] = $this->selectedWork;
        
        $this->unassignedWorks = array_filter($this->unassignedWorks, fn ($w) => !$this->areWorksEqual($w, $this->selectedWork));
        $this->selectedWork = null;

        // Rinfreschiamo i calcoli della riga
        $row->refresh((float) $this->bancaleCost);
    }

    private function areWorksEqual(?array $a, ?array $b): bool
    {
        if ($a === null || $b === null) return false;
        return ($a['id'] ?? null) == ($b['id'] ?? null);
    }

public function printSplitTable()
{
    // 1. Recuperiamo la collezione delle liquidazioni per i totali a fondo pagina
    // Usiamo pluck per estrarre solo la colonna economica
    $liquidations = $this->matrixTable->rows->pluck('liquidation');
    
    // Calcoliamo i totali generali (N, X, P, Netto) usando il metodo statico del DTO
    $totals = LiquidationResult::aggregateTotals($liquidations);

    // 2. Prepariamo la matrice dei dati per la vista PDF
    $matrixData = $this->matrixTable->rows->map(function(LicenseRow $l) {
        $liq = $l->liquidation;

        // SE Livewire ha degradato l'oggetto a stdClass (accade durante l'hydration),
        // lo ricostruiamo come LiquidationResult per riavere accesso ai suoi metodi.
        if ($liq instanceof \stdClass) {
            $liq = LiquidationResult::fromLivewire((array) $liq);
        }

        // Se per qualche motivo è null o non valido, creiamo un risultato vuoto per evitare crash
        if (!$liq) {
            $liq = new LiquidationResult();
        }

        /**
         * Usiamo il metodo toPrintParams() del DTO che centralizza tutte le chiavi 
         * richieste dalla vista (n_count, netto_raw, ecc.) evitando duplicazione di logica.
         */
        return $liq->toPrintParams([
            'license_number' => $l->user['license_number'] ?? '—',
            'worksMap'       => $l->worksMap,
        ]);
    })->values()->toArray();

    // 3. Salviamo i dati in Sessione Flash per il controller che genera il PDF
    /*Session::flash('pdf_generate', [
        'view' => 'pdf.split-table',
        'data' => [
            'matrix'      => $matrixData,
            'totals'      => $totals,
            'bancaleCost' => (float) $this->bancaleCost,
            'generatedBy' => Auth::user()->name ?? 'Sistema',
            'generatedAt' => now(),
            'date'        => today(),
        ],
        'filename' => 'ripartizione_' . today()->format('Ymd') . '.pdf',
    ]);

    // Reindirizziamo alla rotta globale di generazione PDF
    return $this->redirectRoute('generate.pdf');
    */

    // Renderizziamo la vista specifica per lo splitter
    $html = view('pdf.split-table', [
        'matrix'      => $matrixData,
        'totals'      => $totals,
        'bancaleCost' => (float) $this->bancaleCost,
        'generatedBy' => Auth::user()->name ?? 'Sistema',
        'generatedAt' => now(),
        'date'        => today(),
    ])->render();

    // Lanciamo lo stesso evento della tabella principale
    $this->dispatch('print-html', html: $html);
}

    public function printAgencyReport(AgencyReportService $service): void
    {
        /**
         * Trasformiamo la collezione di oggetti LicenseRow in un array semplice.
         * Il nuovo AgencyReportService si aspetta la struttura originale (user + worksMap)
         * per poter gestire autonomamente il filtraggio e il raggruppamento.
         */
        $dataForReport = $this->matrixTable->rows->map(function(LicenseRow $row) {
            return [
                'user'     => $row->user,
                'worksMap' => $row->worksMap,
            ];
        })->toArray();

        // Il Service ora gestisce internamente flatMap, filter e groupBy
        $agencyReport = $service->generate($dataForReport);

        /*Session::flash('pdf_generate', [
            'view' => 'pdf.agency-report',
            'data' => [
                'agencyReport'  => $agencyReport,
                'generatedBy'   => Auth::user()->name ?? 'Sistema',
                'date'          => today()->format('d/m/Y'),
                'generatedAt'   => now()
            ],
            'filename' => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
        ]);

        $this->redirectRoute('generate.pdf');*/

        // Renderizziamo la vista specifica per lo splitter
        $html = view('pdf.agency-report', [
            'agencyReport'  => $agencyReport,
            'generatedBy'   => Auth::user()->name ?? 'Sistema',
            'date'          => today()->format('d/m/Y'),
            'generatedAt'   => now()
        ])->render();

        // Lanciamo lo stesso evento della tabella principale
        $this->dispatch('print-html', html: $html);
    }

    public function render()
    {
        return view('livewire.table-manager.matrix-preview');
    }
}