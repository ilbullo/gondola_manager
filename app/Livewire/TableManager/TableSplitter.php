<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, WorkAssignment};
use App\Services\WorkSplitterService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Collection;

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

    /**
     * Prepara i dati e genera il PDF della tabella ripartita (A4 orizzontale, B/N).
     */
    public function printSplitTable(): void
    {
        // Ricalcola la tabella per assicurarsi che l'output sia l'ultima versione
        $this->generateTable();

        $data = [
            'splitTable' => $this->splitTable,
            'bancaleCost' => $this->bancaleCost,
            'excludedFromA' => $this->excludedFromA,
            'timestamp' => today()->format('d/m/Y H:i'),
            // Il resto dei dati che la vista PDF richiede
        ];

        // Invia un evento per avviare la generazione del PDF (da intercettare via JS/Controller)
        $this->dispatch('printPdf', [
            'view' => 'pdf.split-table', // Vista da creare (punto 3)
            'data' => $data,
            'filename' => 'ripartizione_' . today()->format('Ymd') . '.pdf',
            'paper' => 'a4',
            'orientation' => 'landscape', // Formato A4 orizzontale
        ]);

        session()->flash('success', 'Preparazione del PDF della tabella ripartita avviata.');
    }

    /**
     * Prepara e raggruppa i dati per il Report Agenzie.
     */
    private function generateAgencyReportData(): array
    {
        // Se la tabella non è ancora generata, la generiamo
        if (empty($this->splitTable)) {
             $this->generateTable();
        }

        $reportWorks = [];

        // Raccogli tutti i lavori assegnati (solo i record principali)
        foreach ($this->splitTable as $licenseRow) {
            $licenseNumber = $licenseRow['license'];

            foreach ($licenseRow['assignments'] as $work) {
                // Solo i WorkAssignment assegnati (non gli stdClass placeholder e solo i 'capo' blocco)
                // Assumo che $work->slot contenga lo slot di partenza e che WorkAssignment abbia 'agency' relationship
                if ($work instanceof WorkAssignment && $work->slot === $work->slot) {

                    // Usiamo l'ID del lavoro originale (se presente) o un identificatore univoco
                    // Se il lavoro è ripartito (X->N/P), usiamo l'ID originale per raggruppare
                    $workKey = $work->original_id ?? $work->id; // Assumendo che ci sia un modo per tracciare l'originale

                    // Ottieni il timestamp formattato o una stringa vuota di fallback
                    $formattedTimestamp = $work->timestamp?->format('YmdHi') ?? '000000000000';

                    // Per il report agenzie, ci concentriamo sui lavori A (che hanno agency) e sui lavori generali
                    $agencyName = $work->agency->name ?? ($work->value === 'A' ? 'Sconosciuta' : 'N/D');

                    // Raggruppa per un identificatore univoco del lavoro (che non cambia tra licenze)
                    $uniqueWorkIdentifier = $agencyName . '_' . $formattedTimestamp . '_' . $work->voucher;

                    if (!isset($reportWorks[$uniqueWorkIdentifier])) {
                        // Inizializza il record per il report
                        $reportWorks[$uniqueWorkIdentifier] = [
                            'agency_name' => $agencyName,
                            'timestamp'   => $work->timestamp,
                            'voucher'     => $work->voucher,
                            'license_numbers' => [],
                            'work_type' => $work->value,
                            'slots_assigned' => 0,
                            'original_work_id' => $workKey,
                        ];
                    }

                    // Aggiungi il numero di licenza
                    $reportWorks[$uniqueWorkIdentifier]['license_numbers'][] = $licenseNumber;
                    $reportWorks[$uniqueWorkIdentifier]['slots_assigned'] += $work->slots_occupied;
                }
            }
        }

        // 3. Post-elaborazione: Rimuovi duplicati di licenza e formatta la lista
        $report = collect($reportWorks)
            ->map(function ($work) {
                // Ordina e unisci i numeri di licenza
                $work['license_numbers'] = collect($work['license_numbers'])->unique()->sort()->implode(', ');
                return $work;
            })
            // Raggruppa per Agenzia per la visualizzazione nel PDF
            ->groupBy('agency_name')
            ->toArray();

        return $report;
    }

    /**
     * Prepara i dati e genera il PDF del Report Agenzie (Punto 2).
     */
    public function printAgencyReport(): void
    {
        $reportData = $this->generateAgencyReportData();

        $data = [
            'agencyReport' => $reportData,
            'timestamp' => today()->format('d/m/Y H:i'),
        ];

        // Invia un evento per avviare la generazione del PDF (da intercettare via JS/Controller)
        $this->dispatch('printPdf', [
            'view' => 'pdf.agency-report', // Vista da creare (punto 4)
            'data' => $data,
            'filename' => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
            'paper' => 'a4',
            'orientation' => 'landscape', // Formato A4 orizzontale
        ]);

        session()->flash('success', 'Preparazione del PDF del report agenzie avviata.');
    }


    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.table-manager.table-splitter');
    }
}
