<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, WorkAssignment};
use App\Services\WorkSplitterService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;   

class TableSplitter extends Component
{
    /** @var float Costo del bancale inserito dall'utente. */
    public float $bancaleCost = 0.0;

    /** @var array<int, mixed> La tabella ripartita finale. */
    public array $splitTable = [];

     /** @var array<int, mixed> La lista delle licenze escluse da ripartizione lavori tipo A. */
    public array $excludedFromA = [];

    // ===================================================================
    // Lifecycle & Data Loading
    // ===================================================================

    public function mount(): void
    {
        $this->generateTable();
    }

//    #[On('tableReset')]
//    #[On('licensesCleared')]
    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {

        $this->splitTable = [];

        // 1. Carica le licenze in servizio oggi (ordinate)
        $licenses = LicenseTable::with('user:id,name,license_number')
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();

        if ($licenses->isEmpty()) {
            return;
        }

        // 2. Carica tutti i lavori ripartibili (non esclusi)
        $sharableWorks = WorkAssignment::whereDate('timestamp', today())
            ->where('excluded', false)
            ->with('agency:id,code')
            ->whereIn('value', ['A', 'X', 'N', 'P'])
            ->get();

        // 3. Usa il servizio per la logica di ripartizione
        $splitter = new WorkSplitterService($licenses, $sharableWorks, $this->excludedFromA);

        // 4. Genera la tabella e la salva nello stato Livewire
        $this->splitTable = $splitter->getSplitTable($this->bancaleCost ?? 0);
    }

    /**
     * Ricalcola la tabella quando il costo del bancale cambia
     */
    public function updatedBancaleCost(): void
    {
        $this->generateTable();
    }

    public function toggleExcludeFromA(int $licenseTableId): void
    {
        if (in_array($licenseTableId, $this->excludedFromA)) {
            $this->excludedFromA = array_diff($this->excludedFromA, [$licenseTableId]);
        } else {
            $this->excludedFromA[] = $licenseTableId;
        }

        // Ripartisci immediatamente
        $this->generateTable();
    }

    // ===================================================================
    // Funzionalità PDF (Punto 1)
    // ===================================================================

public function printSplitTable(): void
{
    $this->generateTable();

    Session::flash('pdf_generate', [
        'view'        => 'pdf.split-table',
        'data'        => [
            'splitTable'  => $this->splitTable,
            'bancaleCost' => $this->bancaleCost,
            'timestamp'   => now()->format('d/m/Y H:i'),
        ],
        'filename'    => 'ripartizione_' . today()->format('Ymd') . '.pdf',
        'orientation' => 'landscape',
    ]);

    // Redirect alla route che genera il PDF
    $this->redirectRoute('generate.pdf');
}

public function printAgencyReport(): void
{
    $this->generateTable();

    Session::flash('pdf_generate', [
        'view'        => 'pdf.agency-report',
        'data'        => [
            'agencyReport' => $this->generateAgencyReportData(),
            'timestamp'    => now()->format('d/m/Y H:i'),
        ],
        'filename'    => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
        'orientation' => 'landscape',
    ]);

    $this->redirectRoute('generate.pdf');
}

private function generateAgencyReportData(): array
{
    if (empty($this->splitTable)) {
        return [];
    }

    $report = [];

    foreach ($this->splitTable as $row) {
        $licenseNumber = $row['license'];

        foreach ($row['assignments'] as $slot => $assignment) {
            // 1. Ignora completamente i placeholder stdClass
            if (!$assignment instanceof \App\Models\WorkAssignment) {
                continue;
            }

            // 2. Prendi SOLO il record che inizia in quella posizione
            //    (il tuo service imposta $assignment->slot = posizione di partenza)
            if ($assignment->slot != $slot) {
                continue; // ← Questa è la riga MAGICA che risolve tutto
            }

            // --- Da qui in poi è sicuro: abbiamo il record principale ---
            $agencyName = $assignment->value === 'A' && $assignment->agency
                ? $assignment->agency->name
                : 'Servizi Generali';

            $voucher = trim($assignment->voucher ?? '');
            $voucherDisplay = $voucher !== '' ? $voucher : 'Senza Voucher';

            // Chiave univoca: agenzia + voucher + timestamp (per lavori identici)
            $key = $agencyName . '||' . $voucherDisplay . '||' . ($assignment->timestamp?->format('YmdHi') ?? '000000');

            if (!isset($report[$key])) {
                $report[$key] = [
                    'agency_name'     => $agencyName,
                    'voucher'         => $voucher !== '' ? $voucher : '-',
                    'voucher_display' => $voucherDisplay,
                    'timestamp'       => $assignment->timestamp,
                    'work_type'       => $assignment->value,
                    'slots'           => 0,
                    'license_numbers' => [],
                ];
            }

            $report[$key]['license_numbers'][] = $licenseNumber;
            $report[$key]['slots'] += $assignment->slots_occupied ?? 1;
        }
    }

    // Formattazione finale
    return collect($report)
        ->map(function ($item) {
            $item['license_numbers'] = collect($item['license_numbers'])
                ->unique()
                ->sort()
                ->implode(', ');
            $item['time'] = $item['timestamp']?->format('H:i') ?? 'N/D';
            return $item;
        })
        ->sortBy('timestamp')
        ->groupBy('agency_name')
        ->map->groupBy('voucher_display')
        ->toArray();
}

    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.table-manager.table-splitter');
    }
}
