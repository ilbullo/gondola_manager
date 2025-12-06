<?php

namespace App\Livewire\TableManager;

use App\Enums\DayType;
use App\Models\{LicenseTable, WorkAssignment};
use App\Services\WorkSplitterService;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class TableSplitter extends Component
{
    public float $bancaleCost = 0.0;
    public array $splitTable = [];
    public array $excludedFromA = [];
    public array $shifts = [];

    //added
    public $matrix;
    public $unassignedWorks;

    // NUOVO: Statistiche di validazione (Expected vs Actual)
    public array $validationStats = [];

    public function mount(): void
    {
        $this->generateTable();
        $licenses = LicenseTable::with([
                'user:id,license_number',
                'works' => fn($q) => $q->whereDate('timestamp', today())
                    ->orderBy('slot')
                    ->with('agency:id,name,code')
            ])
            ->withCount(['works' => fn($q) => $q->whereDate('timestamp', today())])
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();

            $licenseTable = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();
            $service = new \App\Services\MatrixSplitterService($licenseTable);

            $this->matrix = $service->matrix->toArray();
            $this->unassignedWorks = $service->unassignedWorks->toArray();
    }

    #[On('callRedistributeWorks')]
    public function generateTable(): void
    {
        $this->splitTable = [];
        $this->validationStats = [];

        $licenses = LicenseTable::with('user:id,name,license_number')
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();

        if ($licenses->isEmpty()) {
            return;
        }

        foreach ($licenses as $license) {
            $currentShift = $this->shifts[$license->id] ?? null;
            if (!$currentShift || !DayType::tryFrom($currentShift)) {
                $this->shifts[$license->id] = DayType::FULL->value;
            }
        }

        $sharableWorks = WorkAssignment::whereDate('timestamp', today())
            ->where('excluded', false)
            ->with('agency:id,name,code')
            ->whereIn('value', ['A', 'X', 'N', 'P'])
            ->orderBy('timestamp')
            ->get();

        $splitter = new WorkSplitterService(
            $licenses,
            $sharableWorks,
            $this->excludedFromA,
            $this->shifts
        );

        // Ottieni tabella ripartita
        $this->splitTable = $splitter->getSplitTable($this->bancaleCost ?? 0);

        // Ottieni statistiche di validazione
        $this->validationStats = $splitter->getValidationStats();
    }

    public function updatedBancaleCost(): void
    {
        $this->generateTable();
    }

    public function updatedShifts(): void
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
        $this->generateTable();
    }

    public function printSplitTable(): void
    {
        $this->generateTable();

        Session::flash('pdf_generate', [
            'view'        => 'pdf.split-table',
            'data'        => [
                'splitTable'  => $this->splitTable,
                'bancaleCost' => $this->bancaleCost,
                'bancaleName' => Auth::user()->name,
                'timestamp'   => now()->format('d/m/Y H:i'),
            ],
            'filename'    => 'ripartizione_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'landscape',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    public function printAgencyReport(): void
    {
        $this->generateTable();
        $agencyReport = $this->prepareAgencyReport();

        Session::flash('pdf_generate', [
            'view'        => 'pdf.agency-report',
            'data'        => [
                'agencyReport' => $agencyReport,
                'timestamp'    => now()->format('d/m/Y H:i'),
            ],
            'filename'    => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'portrait',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    private function prepareAgencyReport(): array
{
    $report = [];

    foreach ($this->splitTable as $tableRow) {
        $licenseNumber = $tableRow['license'];

        foreach ($tableRow['assignments'] as $slot => $assignment) {
            if (
                !$assignment instanceof WorkAssignment ||
                $assignment->slot !== $slot ||
                $assignment->value !== 'A'
            ) {
                continue;
            }

            $agencyName = $assignment->agency->name ?? 'N/A';

            // Normalizzazione voucher
            $voucher = trim($assignment->voucher ?? '') ?: '-';

            // Formattazione orario
            $time = $assignment->timestamp instanceof \Carbon\Carbon
                ? $assignment->timestamp->format('H:i')
                : \Carbon\Carbon::parse($assignment->timestamp)->format('H:i');

            // Timestamp intero per chiave di fallback
            $timestampKey = $assignment->timestamp instanceof \Carbon\Carbon
                ? $assignment->timestamp->format('YmdHi')
                : \Carbon\Carbon::parse($assignment->timestamp)->format('YmdHi');

            /*
            |--------------------------------------------------------------------------
            | NUOVA LOGICA GRUPPI
            |--------------------------------------------------------------------------
            | - Se esiste un voucher → raggruppa per voucher (ignorando orario)
            | - Se NON esiste un voucher → raggruppa per timestamp come prima
            |--------------------------------------------------------------------------
            */
            if ($voucher !== '-') {
                $key = $agencyName . '|voucher:' . $voucher;
            } else {
                $key = $agencyName . '|time:' . $timestampKey;
            }

            // Inizializza gruppo
            if (!isset($report[$key])) {
                $report[$key] = [
                    'agency_name'     => $agencyName,
                    'time'            => $time,
                    'voucher'         => $voucher,
                    'license_numbers' => [],
                ];
            }

            // Aggiunge la licenza al gruppo
            $report[$key]['license_numbers'][] = $licenseNumber;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Formattazione finale
    |--------------------------------------------------------------------------
    */
    return collect($report)
        ->map(function ($item) {
            $item['license_numbers'] = collect($item['license_numbers'])
                ->unique()
                ->sort()
                ->implode(', ');
            return $item;
        })
        // Ordina per orario (ha effetto solo sui gruppi senza voucher)
        ->sortBy('time')
        ->values()
        ->groupBy('agency_name')
        ->map(fn(Collection $group) => $group->values())
        ->toArray();
}


    public function render()
    {
        return view('livewire.table-manager.matrix-preview',[
            'matrix' => $this->matrix,
            'unassignedWorks' => $this->unassignedWorks,
        ]);
        //return view('livewire.table-manager.table-splitter');
    }
}
