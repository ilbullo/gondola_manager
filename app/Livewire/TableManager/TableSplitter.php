<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class TableSplitter extends Component
{
    public float $bancaleCost = 0.0;
    public array $matrix = [];
    public array $unassignedWorks = [];
    public ?array $selectedWork = null;

    public function mount(): void
    {
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

        $licenseTable = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();
        $service = new \App\Services\MatrixSplitterService($licenseTable);

        $this->matrix = $service->matrix->toArray();
        $this->unassignedWorks = $service->unassignedWorks->toArray();
        $this->selectedWork = null;
    }

    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {
        $this->loadMatrix();
        $this->dispatch('matrix-updated');
    }

    // RIMUOVI LAVORO
    public function removeWork($licenseKey, $slotIndex): void
{
    $work = $this->matrix[$licenseKey]['worksMap'][$slotIndex] ?? null;
    if (!$work) return;

    // Usa il tuo ModalConfirm globale
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
        $work       = $payload['work'];

        // Rimuovi dalla matrice
        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = null;

        // Rimetti nei lavori non assegnati
        $this->unassignedWorks[] = $work;

        // Opzionale: messaggio di successo
        $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);

        // Aggiorna la vista
        $this->dispatch('matrix-updated');
    }

    // SELEZIONA LAVORO (con toggle)
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

    // DESELEZIONA
    public function deselectWork(): void
    {
        $this->selectedWork = null;
        $this->dispatch('work-deselected');
    }

    // ASSEGNA A SLOT
    public function assignToSlot($licenseKey, $slotIndex): void
    {
        if (!$this->selectedWork) {
            $this->dispatch('notify', ['message' => 'Seleziona un lavoro prima!', 'type' => 'warning']);
            return;
        }

        if (!is_null($this->matrix[$licenseKey]['worksMap'][$slotIndex] ?? null)) {
            $this->dispatch('notify', ['message' => 'Slot già occupato!', 'type' => 'error']);
            return;
        }

        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = $this->selectedWork;

        // Rimuovi da unassigned usando chiave univoca (id o combinazione campi)
        $this->unassignedWorks = array_filter($this->unassignedWorks, fn($w) => !$this->areWorksEqual($w, $this->selectedWork));

        $this->selectedWork = null;
        $this->dispatch('matrix-updated');
        $this->dispatch('work-deselected');
    }

    // Helper per confrontare due lavori (perché array non sono ===)
    private function areWorksEqual(?array $a, ?array $b): bool
    {
        if ($a === null || $b === null) return false;
        return ($a['id'] ?? null) == ($b['id'] ?? null)
            || ($a['value'] ?? '') === ($b['value'] ?? '')
               && ($a['timestamp'] ?? '') === ($b['timestamp'] ?? '');
    }

    // PDF FUNZIONANTI
public function printSplitTable(): void
{
    // Prepara i dati esattamente come li usi nella vista attuale
    $matrixData = collect($this->matrix)->map(function ($license) {
        return [
            'license_number' => $license['user']['license_number'] ?? '—',
            'worksMap'       => $license['worksMap'],
            'slots'          => $license['slots'] ?? 25,
            'n_count'        => collect($license['worksMap'])->where('value', 'N')->count(),
            'p_count'        => collect($license['worksMap'])->where('value', 'P')->count(),
            'occupied'       => collect($license['worksMap'])->filter()->count(),
            'cash_total'     => collect($license['worksMap'])->where('value', 'X')->sum('amount') ?? 0,
        ];
    })->sortBy('license_number')->values();

    // Calcola totali per il footer del PDF (opzionale ma utile)
    $totalN = $matrixData->sum('n_count');
    $totalP = $matrixData->sum('p_count');
    $totalOccupied = $matrixData->sum('occupied');
    $totalCash = $matrixData->sum('cash_total') - $this->bancaleCost;

    Session::flash('pdf_generate', [
        'view'        => 'pdf.split-table',
        'data'        => [
            'matrix'       => $matrixData,
            'bancaleCost'  => $this->bancaleCost,
            'totalN'       => $totalN,
            'totalP'       => $totalP,
            'totalOccupied'=> $totalOccupied,
            'totalCash'    => $totalCash,
            'generatedBy'  => Auth::user()->name,
            'generatedAt'  => now()->format('d/m/Y H:i'),
            'date'         => now()->format('d/m/Y'),
        ],
        'filename'     => 'ripartizione_lavori_' . now()->format('Ymd') . '.pdf',
        'orientation'  => 'landscape',
    ]);

    $this->redirectRoute('generate.pdf');
}

    public function printAgencyReport(): void
    {
        $agencyReport = $this->prepareAgencyReport();

        Session::flash('pdf_generate', [
            'view' => 'pdf.agency-report',
            'data' => [
                'agencyReport' => $agencyReport,
                'timestamp'   => now()->format('d/m/Y H:i'),
            ],
            'filename' => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'portrait',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    private function prepareAgencyReport(): array
    {
        $report = [];

        foreach ($this->matrix as $license) {
            $licenseNumber = $license['user']['license_number'] ?? 'N/D';

            foreach ($license['worksMap'] as $slot => $work) {
                if (!$work || ($work['value'] ?? '') !== 'A') continue;

                $agencyName = $work['agency']['name'] ?? 'N/A';
                $voucher   = $work['voucher'] ?? '-';
                $time = \Carbon\Carbon::parse($work['timestamp'])->format('H:i');

                $key = $agencyName . '|' . \Carbon\Carbon::parse($work['timestamp'])->format('YmdHi') . '|' . $voucher;

                if (!isset($report[$key])) {
                    $report[$key] = [
                        'agency_name'     => $agencyName,
                        'time'           => $time,
                        'voucher'        => $voucher,
                        'license_numbers' => [],
                    ];
                }
                $report[$key]['license_numbers'][] = $licenseNumber;
            }
        }

        return collect($report)
            ->map(fn($item) => [
                ...$item,
                'license_numbers' => collect($item['license_numbers'])->unique()->sort()->implode(', ')
            ])
            ->sortBy('time')
            ->values()
            ->groupBy('agency_name')
            ->map(fn($g) => $g->values()->toArray())
            ->toArray();
    }

    public function render()
    {
        //return view('livewire.table-manager.table-splitter'); // ← usa questa vista!
        return view('debug.matrix-preview');
    }
}
