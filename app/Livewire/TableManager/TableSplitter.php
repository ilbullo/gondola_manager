<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class TableSplitter extends Component
{
    /**
     * Costo del bancale, sottratto dal totale in fase di stampa PDF.
     */
    public float $bancaleCost = 0.0;

    /**
     * Matrice principale delle licenze con gli slot assegnati.
     * Popolata dalla MatrixSplitterService.
     */
    public array $matrix = [];

    /**
     * Lavori non assegnati presenti nella giornata corrente.
     * Trovati dal MatrixSplitterService.
     */
    public array $unassignedWorks = [];

    /**
     * Lavoro selezionato dall’utente per essere assegnato.
     * Null se non è selezionato nulla.
     */
    public ?array $selectedWork = null;

    // ======================================================================
    // Lifecycle
    // ======================================================================

    /**
     * Carica la matrice iniziale all’avvio del componente.
     */
    public function mount(): void
    {
        $this->loadMatrix();
    }

    /**
     * Prepara la matrice completa leggendo la tabella delle licenze,
     * trasformandola tramite il MatrixSplitterService.
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

        // Converte i modelli in array normalizzati con la risorsa
        $licenseTable = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();

        // Passaggio attraverso il servizio di splitting
        $service = new \App\Services\MatrixSplitterService($licenseTable);

        $this->matrix = $service->matrix->toArray();
        $this->unassignedWorks = $service->unassignedWorks->toArray();
        $this->selectedWork = null;
    }

    // ======================================================================
    // Eventi Livewire
    // ======================================================================

    /**
     * Rigenera la matrice quando viene emesso l'evento di redistribuzione.
     */
    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {
        $this->loadMatrix();
        $this->dispatch('matrix-updated');
    }

    // ======================================================================
    // Gestione rimozione lavori da slot
    // ======================================================================

    /**
     * Apre un modal di conferma per rimuovere un lavoro da uno slot.
     */
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

    /**
     * Conferma la rimozione del lavoro:  
     * - libera lo slot  
     * - rimette il lavoro nei "non assegnati"
     */
    #[On('confirmed-remove')]
    public function confirmedRemove(array $payload): void
    {
        $licenseKey = $payload['licenseKey'];
        $slotIndex  = $payload['slotIndex'];
        $work       = $payload['work'];

        // Svuota lo slot nella matrice
        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = null;

        // Riaggiunge il lavoro ai non assegnati
        $this->unassignedWorks[] = $work;

        $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);
        $this->dispatch('matrix-updated');
    }

    // ======================================================================
    // Gestione selezione / assegnazione lavori
    // ======================================================================

    /**
     * Seleziona un lavoro dalla lista non assegnata.
     * Toggle: se cliccato di nuovo viene deselezionato.
     */
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

    /**
     * Deseleziona il lavoro attualmente selezionato.
     */
    public function deselectWork(): void
    {
        $this->selectedWork = null;
        $this->dispatch('work-deselected');
    }

    /**
     * Assegna il lavoro selezionato ad uno slot libero della matrice.
     */
    public function assignToSlot($licenseKey, $slotIndex): void
    {
        if (!$this->selectedWork) {
            $this->dispatch('notify', [
                'message' => 'Seleziona un lavoro prima!',
                'type' => 'warning'
            ]);
            return;
        }

        if (!is_null($this->matrix[$licenseKey]['worksMap'][$slotIndex] ?? null)) {
            $this->dispatch('notify', [
                'message' => 'Slot già occupato!',
                'type' => 'error'
            ]);
            return;
        }

        // Assegna il lavoro
        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = $this->selectedWork;

        // Rimuove il lavoro dalla lista dei non assegnati
        $this->unassignedWorks = array_filter(
            $this->unassignedWorks,
            fn ($w) => !$this->areWorksEqual($w, $this->selectedWork)
        );

        $this->selectedWork = null;

        $this->dispatch('matrix-updated');
        $this->dispatch('work-deselected');
    }

    /**
     * Confronta due lavori verificando che abbiano lo stesso id.
     */
    private function areWorksEqual(?array $a, ?array $b): bool
    {
        if ($a === null || $b === null) return false;
        return ($a['id'] ?? null) == ($b['id'] ?? null);
    }

    // ======================================================================
    // Esportazione PDF
    // ======================================================================

    /**
     * Stampa PDF della tabella di ripartizione lavori.
     * I dati vengono messi in sessione e letti dal controller PDF.
     */
    public function printSplitTable(): void
    {
        $matrixData = collect($this->matrix)->map(function ($license) {
            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'worksMap'       => $license['worksMap'],
                'slots'          => $license['slots'] ?? config('constants.matrix.total_slots'),
                'n_count'        => collect($license['worksMap'])->where('value', 'N')->count(),
                'p_count'        => collect($license['worksMap'])->where('value', 'P')->count(),
                'occupied'       => collect($license['worksMap'])->filter()->count(),
                'cash_total'     => collect($license['worksMap'])->where('value', 'X')->sum('amount') ?? 0,
            ];
        })->sortBy('license_number')->values();

        // Calcoli riepilogativi usati nel footer del PDF
        $totalN       = $matrixData->sum('n_count');
        $totalP       = $matrixData->sum('p_count');
        $totalOccupied = $matrixData->sum('occupied');
        $totalCash    = $matrixData->sum('cash_total') - $this->bancaleCost;

        Session::flash('pdf_generate', [
            'view'        => 'pdf.split-table',
            'data'        => [
                'matrix'        => $matrixData,
                'bancaleCost'   => $this->bancaleCost,
                'totalN'        => $totalN,
                'totalP'        => $totalP,
                'totalOccupied' => $totalOccupied,
                'totalCash'     => $totalCash,
                'generatedBy'   => Auth::user()->name,
                'generatedAt'   => now()->format('d/m/Y H:i'),
                'date'          => now()->format('d/m/Y'),
            ],
            'filename'     => 'ripartizione_lavori_' . now()->format('Ymd') . '.pdf',
            'orientation'  => 'landscape',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    /**
     * Stampa PDF raggruppato per agenzia.
     */
    public function printAgencyReport(): void
    {
        $agencyReport = $this->prepareAgencyReport();

        Session::flash('pdf_generate', [
            'view'        => 'pdf.agency-report',
            'data'        => [
                'agencyReport'  => $agencyReport,
                'generatedBy'   => Auth::user()->name,
                'generatedAt'   => now()->format('d/m/Y H:i'),
                'date'          => today()->format('d/m/Y'),
                'totalLicenses' => collect($this->matrix)->count(),
            ],
            'filename'     => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
            'orientation'  => 'portrait',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    /**
     * Prepara una struttura dati raggruppata per Agenzia → Voucher → Orario,
     * con elenco delle licenze coinvolte.
     */
    private function prepareAgencyReport(): array
    {
        $services = [];

        foreach ($this->matrix as $licenseRow) {
            $licenseNumber = $licenseRow['user']['license_number'] ?? 'N/D';

            foreach ($licenseRow['worksMap'] as $work) {
                if (empty($work) || ($work['value'] ?? '') !== 'A') continue;

                $agencyName = $work['agency']['name']
                    ?? $work['agency_name']
                    ?? $work['agency']
                    ?? 'Agenzia sconosciuta';

                $voucher = trim($work['voucher'] ?? '') ?: '–';
                $time    = \Carbon\Carbon::parse($work['timestamp'] ?? now())->format('H:i');

                // Chiave univoca del servizio
                $key = $agencyName . '|' . $voucher . '|' . $time;

                if (!isset($services[$key])) {
                    $services[$key] = [
                        'agency_name' => $agencyName,
                        'voucher'     => $voucher,
                        'time'        => $time,
                        'licenses'    => [],
                    ];
                }

                $services[$key]['licenses'][] = $licenseNumber;
            }
        }

        // Ordina per orario
        uasort($services, fn ($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));

        // Normalizza la struttura finale
        return collect($services)->map(function ($item) {
            $licenses = collect($item['licenses'])->unique()->sort()->values();

            return [
                'agency_name' => $item['agency_name'],
                'voucher'     => $item['voucher'],
                'time'        => $item['time'],
                'licenses'    => $licenses->implode(' - '),
                'count'       => $licenses->count(),
            ];
        })->values()->toArray();
    }

    // ======================================================================
    // Render
    // ======================================================================

    /**
     * Render della vista del Table Splitter.
     */
    public function render()
    {
        return view('livewire.table-manager.matrix-preview');
    }
}
