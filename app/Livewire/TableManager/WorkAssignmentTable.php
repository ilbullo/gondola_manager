<?php

namespace App\Livewire\TableManager;

use App\Http\Resources\LicenseResource;
use App\Models\{Agency, LicenseTable, WorkAssignment};
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
        $this->dispatch('toggleLoading', true);

        $licenseTableId = $payload['licenseTableId'] ?? null;
        $slot           = $payload['slot'] ?? null;

        if (!$licenseTableId || !$slot) {
            $this->errorMessage = 'Dati mancanti per rimuovere l\'assegnazione.';
            $this->dispatch('toggleLoading', false);
            return;
        }

        try {
            DB::transaction(function () use ($licenseTableId, $slot) {
                $assignment = WorkAssignment::where('license_table_id', $licenseTableId)
                    ->where('slot', $slot)
                    ->whereDate('timestamp', today())
                    ->first();

                if ($assignment) {
                    WorkAssignment::where('license_table_id', $licenseTableId)
                        ->whereBetween('slot', [$slot, $slot + $assignment->slots_occupied - 1])
                        ->whereDate('timestamp', today())
                        ->delete();
                }
            });

            $this->refreshTable();
            $this->errorMessage = '';
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Errore durante la rimozione del lavoro.';
        }

        $this->dispatch('toggleLoading', false);
    }

    public function assignWork(int $licenseTableId, int $slot): void
    {
        if (!$this->selectedWork || empty($this->selectedWork['value'])) {
            $this->errorMessage = 'Seleziona un lavoro valido dalla sidebar.';
            return;
        }

        if (!in_array($this->selectedWork['value'], ['A', 'X', 'P', 'N'])) {
            $this->errorMessage = 'Tipo di lavoro non valido.';
            return;
        }

        if ($slot < 1 || $slot > 25) {
            $this->errorMessage = 'Slot non valido.';
            return;
        }

        $this->dispatch('toggleLoading', true);

        $slotsOccupied = $this->selectedWork['slotsOccupied'] ?? 1;

        $conflict = WorkAssignment::where('license_table_id', $licenseTableId)
            ->whereDate('timestamp', today())
            ->where(function ($q) use ($slot, $slotsOccupied) {
                $q->where('slot', '<=', $slot + $slotsOccupied - 1)
                  ->whereRaw('slot + slots_occupied - 1 >= ?', [$slot]);
            })
            ->exists();

        if ($conflict) {
            $this->errorMessage = 'Lo slot è già occupato o si sovrappone.';
            $this->dispatch('toggleLoading', false);
            return;
        }

        $this->saveAssignment($licenseTableId, $slot, $slotsOccupied);
    }

    public function openConfirmRemove(int $licenseTableId, int $slot): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Vuoi rimuovere il lavoro da questa cella?',
            'confirmEvent' => 'confirmRemoveAssignment',
            'payload'      => compact('licenseTableId', 'slot'),
        ]);
    }

    // ===================================================================
    // Private Helpers
    // ===================================================================

    private function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied): void
    {
        try {
            $agencyId = null;
            if ($this->selectedWork['value'] === 'A' && !empty($this->selectedWork['agencyName'])) {
                $agencyId = Agency::where('name', $this->selectedWork['agencyName'])
                    ->value('id');
            }

            WorkAssignment::create([
                'license_table_id' => $licenseTableId,
                'agency_id'        => $agencyId,
                'slot'             => $slot,
                'value'            => $this->selectedWork['value'],
                'amount'           => $this->selectedWork['amount'] ?? 90,
                'voucher'          => $this->selectedWork['voucher'] ?? null,
                'slots_occupied'   => $slotsOccupied,
                'excluded'         => $this->selectedWork['excluded'] ?? false,
                'shared_from_first'=> $this->selectedWork['sharedFromFirst'] ?? false,
                'timestamp'        => today(),
            ]);

            $this->refreshTable();
            $this->errorMessage = '';
            $this->dispatch('workAssigned');
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Errore durante l\'assegnazione del lavoro.';
        }

        $this->dispatch('toggleLoading', false);
    }

    private function refreshTable(): void
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

    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.table-manager.work-assignment-table');
    }
}