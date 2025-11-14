<?php

namespace App\Livewire\TableManager;

use Livewire\Component;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Illuminate\Support\Facades\Log;

class WorkAssignmentTable extends Component
{
    public $licenses = [];
    public $assignments = [];
    public $selectedWork = null;
    public $errorMessage = '';
    public $confirmOverwrite = false;
    public $cellToOverwrite = null;

    protected $listeners = [
        'workSelected' => 'handleWorkSelected',
    ];

    public function mount()
    {
        $this->loadLicenses();
        $this->loadAssignments();
    }

    public function loadLicenses()
    {
        $this->licenses = LicenseTable::whereDate('date', now()->toDateString())
            ->with('user')
            ->orderBy('order')
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'user_id' => $license->user_id,
                    'user'  => $license->user
                ];
            })
            ->toArray();
    }

    public function loadAssignments()
    {
        $this->assignments = WorkAssignment::whereDate('timestamp', now()->toDateString())
            ->get()
            ->groupBy('license_id')
            ->map(function ($group) {
                return $group->keyBy('slot')->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'value' => $assignment->value,
                        'label' => $this->getWorkLabel($assignment->value),
                        'voucher' => $assignment->voucher,
                        'agency_name' => $assignment->agency_name,
                        'slots_occupied' => $assignment->slots_occupied,
                    ];
                })->toArray();
            })
            ->toArray();
    }

    public function handleWorkSelected($work)
    {
        $this->selectedWork = $work;
        $this->errorMessage = '';
        $this->confirmOverwrite = false;
        $this->cellToOverwrite = null;
    }

    public function assignWork($licenseId, $slot)
    {
        if (!$this->selectedWork || !$this->selectedWork['value']) {
            $this->errorMessage = 'Seleziona un lavoro dalla sidebar prima di assegnare.';
            $this->dispatch('startLoading');
            $this->dispatch('stopLoading');
            return;
        }

        $this->dispatch('startLoading');

        // Controlla se la cella è occupata
        if (isset($this->assignments[$licenseId][$slot])) {
            $this->confirmOverwrite = true;
            $this->cellToOverwrite = ['license_id' => $licenseId, 'slot' => $slot];
            $this->dispatch('stopLoading');
            return;
        }

        $this->saveAssignment($licenseId, $slot);
    }

    public function confirmOverwrite()
    {
        if ($this->cellToOverwrite) {
            $this->dispatch('startLoading');
            $this->saveAssignment($this->cellToOverwrite['license_id'], $this->cellToOverwrite['slot'], true);
            $this->confirmOverwrite = false;
            $this->cellToOverwrite = null;
        }
        $this->dispatch('stopLoading');
    }

    public function cancelOverwrite()
    {
        $this->confirmOverwrite = false;
        $this->cellToOverwrite = null;
        $this->errorMessage = '';
    }

    protected function saveAssignment($licenseId, $slot, $overwrite = false)
    {
        try {
            $license = LicenseTable::where('license_id', $licenseId)
                ->whereDate('date', now()->toDateString())
                ->firstOrFail();

            if ($overwrite) {
                WorkAssignment::where('license_id', $licenseId)
                    ->where('slot', $slot)
                    ->whereDate('timestamp', now()->toDateString())
                    ->delete();
            }

            WorkAssignment::create([
                'license_id' => $licenseId,
                'user_id' => $license->user_id,
                'agency_id' => $this->selectedWork['agencyName'] ? ($this->getAgencyId($this->selectedWork['agencyName']) ?? null) : null,
                'slot' => $slot,
                'value' => $this->selectedWork['value'],
                'voucher' => $this->selectedWork['voucher'],
                'slots_occupied' => $this->selectedWork['slotsOccupied'] ?? 1,
                'timestamp' => now(),
            ]);

            $this->loadAssignments();
            $this->errorMessage = '';
        } catch (\Exception $e) {
            Log::error('Failed to assign work', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Errore durante l\'assegnazione del lavoro. Riprova.';
        }

        $this->dispatch('stopLoading');
    }

    public function removeAssignment($licenseId, $slot)
    {
        $this->dispatch('startLoading');
        try {
            WorkAssignment::where('license_id', $licenseId)
                ->where('slot', $slot)
                ->whereDate('timestamp', now()->toDateString())
                ->delete();

            $this->loadAssignments();
            $this->errorMessage = '';
        } catch (\Exception $e) {
            Log::error('Failed to remove assignment', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Errore durante la rimozione dell\'assegnazione. Riprova.';
        }
        $this->dispatch('stopLoading');
    }

    protected function getAgencyId($agencyName)
    {
        return \App\Models\Agency::where('name', $agencyName)->first()?->id;
    }

    protected function getWorkLabel($value)
    {
        $workType = collect(\App\Livewire\Layout\Sidebar::$config['work_types'])
            ->firstWhere('value', $value);
        return $workType ? $workType['label'] : $value;
    }

    public function render()
    {
        return view('livewire.table-manager.work-assignment-table');
    }
}
