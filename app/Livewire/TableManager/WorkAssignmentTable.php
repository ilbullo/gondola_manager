<?php

namespace App\Livewire\TableManager;

use App\Http\Resources\LicenseResource;
use App\Models\{Agency, LicenseTable, WorkAssignment};
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class WorkAssignmentTable extends Component
{
    /** @var array<int, mixed> */
    public array $licenses = [];

    /** @var array|null */
    public ?array $selectedWork = null;

    public string $errorMessage = '';

    // ===================================================================
    // Lifecycle
    // ===================================================================

    public function mount(): void
    {
        // La chiamata a refreshTable() qui attiverà il wire:loading 
        // che si disattiverà automaticamente alla fine del mount.
        $this->refreshTable();
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

    #[On('workSelected')]
    public function handleWorkSelected(?array $work): void
    {
        $this->selectedWork = $work;
        $this->errorMessage = '';
    }

    #[On('confirmRemoveAssignment')]
    public function removeAssignment(array $payload): void
    {
        // Rimosse chiamate esplicite a dispatch('toggleLoading', ...)
        // perché in conflitto con wire:loading.flex

        $licenseTableId = $payload['licenseTableId'] ?? null;

        if (!$licenseTableId) {
            $this->errorMessage = 'Dati mancanti per rimuovere l\'assegnazione.';
            return;
        }

        $this->dispatch('closeWorkInfoModal');

        try {
            // Uso sicuro di destroy per eliminare l'assegnazione
            $deleted = WorkAssignment::destroy($licenseTableId);

            if ($deleted > 0) {
                $this->refreshTable();
                $this->errorMessage = '';
            } else {
                $this->errorMessage = 'Lavoro già rimosso o ID non trovato.';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Errore durante la rimozione del lavoro: ' . $e->getMessage();
        }
    }

    public function assignWork(int $licenseTableId, int $slot): void
    {
        if (!$this->selectedWork || empty($this->selectedWork['value'])) {
            $this->errorMessage = 'Seleziona un lavoro valido dalla sidebar.';
            return;
        }

        $this->saveAssignment($licenseTableId, $slot, $this->selectedWork['slotsOccupied'] ?? 1);
    }

    public function openInfoBox($workId)
    {
        // wire:loading si attiverà automaticamente
        $this->dispatch('showWorkInfo', $workId);
    }

    private function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied): void
    {
        try {
            $agencyId = null;
            if ($this->selectedWork['value'] === 'A' && !empty($this->selectedWork['agencyName'])) {
                $agency = Agency::where('name', $this->selectedWork['agencyName'])->first();
                $agencyId = $agency?->id; // Uso l'operatore nullsafe per evitare eccezioni
            }

            // Verifica conflitti (Logica essenziale mantenuta)
            $conflict = WorkAssignment::where('license_table_id', $licenseTableId)
                ->whereDate('timestamp', today())
                ->where(function ($q) use ($slot, $slotsOccupied) {
                    $q->where('slot', '<=', $slot + $slotsOccupied - 1)
                        ->whereRaw('slot + slots_occupied - 1 >= ?', [$slot]);
                })
                ->exists();

            if ($conflict) {
                $this->errorMessage = 'Lo slot è già occupato o si sovrappone.';
                return;
            }

            // Creazione del nuovo record
            WorkAssignment::create([
                'license_table_id' => $licenseTableId,
                'agency_id'        => $agencyId,
                'slot'             => $slot,
                'value'            => $this->selectedWork['value'],
                'amount'           => $this->selectedWork['amount'] ?? 90,
                'voucher'          => $this->selectedWork['voucher'] ?? null,
                'slots_occupied'   => $slotsOccupied,
                'excluded'         => $this->selectedWork['excluded'] ?? false,
                'shared_from_first' => $this->selectedWork['sharedFromFirst'] ?? false,
                'timestamp'        => now(),
            ]);

            $this->refreshTable();
            $this->errorMessage = '';
            $this->dispatch('workAssigned');
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Errore durante l\'assegnazione del lavoro: ' . $e->getMessage();
        }
    }

    #[On('refreshTableBoard')]
    public function refreshTable(): void
    {
        $licenses = LicenseTable::with([
            'user:id,license_number',
            'works' => fn($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code')
        ])
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();

        $this->licenses = LicenseResource::collection($licenses)->resolve();
    }

    #[On('printWorksTable')]    
    public function printTable(): void
    {
        // Usa gli stessi dati già calcolati nel component
        $matrixData = collect($this->licenses)->map(function ($license) {
            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'worksMap'       => $license['worksMap'],
            ];
        })->sortBy('user.license_number')->values();

        Session::flash('pdf_generate', [
            'view'       => 'pdf.work-assignment-table', // crea questa vista (vedi sotto)
            'data'       => [
                'matrix'      => $matrixData,
                'generatedBy' => Auth::user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'date'        => today()->format('d/m/Y'),
            ],
            'filename'    => 'tabella_assegnazione_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'landscape',
            'paper'       => 'a2', // o 'a1' se serve più spazio
        ]);

        $this->redirectRoute('generate.pdf');
    }

    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.table-manager.work-assignment-table');
    }
}
