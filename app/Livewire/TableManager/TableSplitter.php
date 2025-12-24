<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\LiquidationService;
use Carbon\Carbon;

class TableSplitter extends Component
{
    /**
     * Costo del bancale, inserito all'apertura del componente.
     */
    public float $bancaleCost = 0.0;

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
            'message' => "Costo bancale impostato a €" . number_format($cost, 2, ',', '.')
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

        $licenseTable = \App\Http\Resources\LicenseResource::collection($licenses)->resolve();
        $service = new \App\Services\MatrixSplitterService($licenseTable);

        $this->matrix = $service->matrix->toArray();
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
        
        $this->unassignedWorks[] = $this->matrix[$licenseKey]['worksMap'][$slotIndex];
        $this->matrix[$licenseKey]['worksMap'][$slotIndex] = null;

        $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);
        $this->dispatch('matrix-updated');
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

        $this->dispatch('matrix-updated');
        $this->dispatch('work-deselected');
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
     * Utilizza LiquidationService per calcolare il netto di ogni licenza.
     */
    public function printSplitTable(): void
    {
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);

        $matrixData = collect($this->matrix)->map(function ($license) use ($defaultAmount) {
            // Calcolo manuale della differenza Wallet specifica per riga
            $nCount = collect($license['worksMap'])->where('value', 'N')->count();
            $theoreticalFromN = $nCount * $defaultAmount;
            $currentWallet = (float) ($license['wallet'] ?? 0);
            $walletDiff = $theoreticalFromN - $currentWallet;

            // Delega al Service per i calcoli economici finali
            $liquidation = LiquidationService::calculate(
                $license['worksMap'], 
                $walletDiff, 
                $this->bancaleCost
            );

            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'worksMap'       => $license['worksMap'],
                'n_count'        => $liquidation['counts']['n'],
                'x_count'        => $liquidation['counts']['x'],
                'shared_count'   => $liquidation['counts']['shared'],
                'wallet_diff'    => $liquidation['money']['wallet_diff'],
                'cash_netto'     => $liquidation['money']['netto'], // Totale da incassare oggi
            ];
        })->values();

        Session::flash('pdf_generate', [
            'view' => 'pdf.split-table',
            'data' => [
                'matrix'        => $matrixData,
                'bancaleCost'   => $this->bancaleCost,
                'totalN'        => $matrixData->sum('n_count'),
                'totalX'        => $matrixData->sum('x_count'),
                'totalCash'     => $matrixData->sum('cash_netto'),
                'generatedBy'   => Auth::user()->name,
                'generatedAt'   => now()->format('d/m/Y H:i'),
                'date'          => today()->format('d/m/Y'),
            ],
            'filename'     => 'ripartizione_' . today()->format('Ymd') . '.pdf',
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
                'agencyReport'  => $agencyReport,
                'generatedBy'   => Auth::user()->name,
                'date'          => today()->format('d/m/Y'),
                'generatedAt'   => now()
            ],
            'filename' => 'report_agenzie_' . today()->format('Ymd') . '.pdf',
        ]);

        $this->redirectRoute('generate.pdf');
    }

    private function prepareAgencyReport(): array
    {
        $services = [];

        foreach ($this->matrix as $licenseRow) {
            $licenseNumber = $licenseRow['user']['license_number'] ?? 'N/D';

            foreach ($licenseRow['worksMap'] as $work) {
                if (empty($work) || ($work['value'] ?? '') !== 'A' || $work['shared_from_first']) continue;

                $agencyName = $work['agency']['name'] ?? $work['agency'] ?? 'Sconosciuta';
                $voucher = trim($work['voucher'] ?? '') ?: '–';
                $timeObj = Carbon::parse($work['timestamp'] ?? now());

                // Logica di raggruppamento voucher o prossimità (5 min)
                $key = ($voucher !== '–') ? $agencyName . '|V:' . $voucher : $agencyName . '|T:' . $timeObj->format('H:i');

                if (!isset($services[$key])) {
                    $services[$key] = [
                        'agency_name' => $agencyName,
                        'voucher'     => $voucher,
                        'time'        => $timeObj->format('H:i'),
                        'licenses'    => [],
                        'count'       => 0,
                    ];
                }

                $services[$key]['licenses'][] = $licenseNumber;
                $services[$key]['count']++;
            }
        }

        uasort($services, fn($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));
        return array_values($services);
    }

    public function render()
    {
        return view('livewire.table-manager.matrix-preview');
    }
}