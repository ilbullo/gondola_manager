<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Http\Resources\LicenseResource;

class WorkAssignmentTable extends Component
{
    public $licenses = [];

    public $selectedWork = null;

    public $errorMessage = '';

    protected $listeners = [
        'workSelected' => 'handleWorkSelected',
        'confirmRemoveAssignment' => 'removeAssignment',
    ];

    public function mount()
    {
        $this->loadLicenses();
    }

public function loadLicenses()
{
    $currentDate = today()->toDateString();

    // Debug: Query diretta su WorkAssignment per verificare slot reali
    $debugAssignments = \App\Models\WorkAssignment::whereDate('timestamp', $currentDate)
        ->orderBy('user_id')
        ->orderBy('slot')
        ->get(['id', 'user_id', 'slot', 'value', 'slots_occupied', 'timestamp'])
        ->groupBy('user_id');

    \Log::info('Debug: Raw WorkAssignments per user_id', [
        'current_date' => $currentDate,
        'assignments_by_user' => $debugAssignments->map(fn($group) => $group->map(fn($assignment) => [
            'id' => $assignment->id,
            'slot' => $assignment->slot,
            'value' => $assignment->value,
            'slots_occupied' => $assignment->slots_occupied,
            'timestamp' => $assignment->timestamp->toDateTimeString(),
        ]))->toArray(),
    ]);

    $query = LicenseTable::select('id', 'user_id', 'order', 'date')
        ->whereDate('date', $currentDate)
        ->with([
            'user' => fn ($query) => $query->select('id', 'license_number'),
            'works' => fn ($query) => $query->select('id', 'user_id', 'slot', 'value', 'slots_occupied', 'timestamp', 'agency_id', 'voucher', 'created_at')
                ->whereDate('timestamp', $currentDate)
                ->orderBy('slot') // Ordine per slot per processare correttamente
                ->with(['agency' => fn ($subQuery) => $subQuery->select('id', 'name', 'code')])
        ])
        ->orderBy('order');

    $licenses = $query->get();

    // Debug: Dati grezzi dei works prima della trasformazione
    $rawWorks = $licenses->flatMap(fn($license) => $license->works->map(fn($work) => [
        'license_id' => $license->id,
        'work_id' => $work->id,
        'slot' => $work->slot,
        'value' => $work->value,
        'slots_occupied' => $work->slots_occupied,
    ]));

    \Log::info('Debug: Raw works from query', [
        'raw_works' => $rawWorks->toArray(),
    ]);

    $this->licenses = LicenseResource::collection($licenses)->resolve();

    // Debug: Esempio di $worksMap dopo trasformazione
    \Log::info('Debug: WorksMap example for first license', [
        'first_license_id' => $this->licenses[0]['id'] ?? null,
        'worksMap' => $this->licenses[0]['worksMap'] ?? [],
    ]);

    return $this->licenses;
}
    public function handleWorkSelected($work)
    {
        $this->selectedWork = $work;
        $this->errorMessage = '';
    }

    public function openConfirmRemove($userId, $slot)
    {
        $this->dispatch('openConfirmModal', [
            'message' => 'Vuoi rimuovere il valore da questa cella?',
            'confirmEvent' => 'confirmRemoveAssignment',
            'payload' => ['userId' => $userId, 'slot' => $slot],
        ]);
    }

public function assignWork($userId, $slot)
{
    if (!$this->selectedWork || !isset($this->selectedWork['value']) || !in_array($this->selectedWork['value'], ['A', 'X', 'P', 'N'])) {
        $this->errorMessage = 'Seleziona un lavoro valido dalla sidebar prima di assegnare.';
        $this->dispatch('startLoading');
        $this->dispatch('stopLoading');
        return;
    }

    $this->dispatch('startLoading');

    // Debug: Log dello slot cliccato
    \Log::info('assignWork called', [
        'user_id' => $userId,
        'slot' => $slot,
        'selectedWork' => $this->selectedWork,
    ]);

    $slotsOccupied = $this->selectedWork['slotsOccupied'] ?? 1;

    // Controllo di sovrapposizione migliorato
    $existing = WorkAssignment::where('user_id', $userId)
        ->whereDate('timestamp', today()->toDateString())
        ->where(function ($query) use ($slot, $slotsOccupied) {
            $query->where('slot', '<=', $slot + $slotsOccupied - 1)
                  ->whereRaw('slot + slots_occupied > ?', [$slot]);
        })
        ->exists();

    if ($existing) {
        $this->errorMessage = 'Lo slot è già occupato o si sovrappone a un altro lavoro. Rimuovi prima il lavoro esistente.';
        \Log::warning('Slot conflict detected', [
            'user_id' => $userId,
            'slot' => $slot,
            'slots_occupied' => $slotsOccupied,
            'existing' => WorkAssignment::where('user_id', $userId)->whereDate('timestamp', today()->toDateString())->get(['slot'])->toArray(),
        ]);
        $this->dispatch('stopLoading');
        return;
    }

    $this->saveAssignment($userId, $slot);
}

protected function saveAssignment($userId, $slot)
{
    try {
        $license = LicenseTable::where('user_id', $userId)
            ->whereDate('date', today()->toDateString())
            ->firstOrFail();

        // Validazione esplicita dello slot (1-25)
        if ($slot < 1 || $slot > 25) {
            throw new \Exception('Slot non valido: ' . $slot);
        }

        $agencyId = null;
        if ($this->selectedWork['value'] === 'A' && !empty($this->selectedWork['agencyName'])) {
            $agencyId = $this->getAgencyId($this->selectedWork['agencyName']);
        }

        \Log::info('Saving WorkAssignment', [
            'user_id' => $userId,
            'slot' => $slot,
            'agency_id' => $agencyId,
            'selectedWork' => $this->selectedWork,
        ]);

        $assignment = WorkAssignment::create([
            'user_id' => $userId,
            'agency_id' => $agencyId,
            'slot' => $slot, // Forzato esplicito
            'value' => $this->selectedWork['value'],
            'voucher' => $this->selectedWork['voucher'] ?? null,
            'slots_occupied' => $this->selectedWork['slotsOccupied'] ?? 1,
            'timestamp' => now()->startOfDay(),
        ]);

        // Verifica post-salvataggio
        $savedAssignment = WorkAssignment::find($assignment->id);
        if ($savedAssignment->slot !== $slot) {
            \Log::error('Slot altered after save', [
                'expected_slot' => $slot,
                'saved_slot' => $savedAssignment->slot,
            ]);
            $savedAssignment->update(['slot' => $slot]);
        }

        \Log::info('WorkAssignment created and verified', [
            'id' => $assignment->id,
            'user_id' => $userId,
            'slot' => $savedAssignment->slot,
            'value' => $savedAssignment->value,
            'agency_id' => $savedAssignment->agency_id,
            'timestamp' => $savedAssignment->timestamp,
        ]);

        $this->loadLicenses();
        $this->errorMessage = '';
        $this->dispatch('$refresh');
    } catch (\Exception $e) {
        \Log::error('Failed to assign work', ['error' => $e->getMessage()]);
        $this->errorMessage = 'Errore durante l\'assegnazione del lavoro: ' . $e->getMessage();
    }

    $this->dispatch('stopLoading');
}

    public function removeAssignment($payload)
    {
        $userId = $payload['userId'];
        $slot = $payload['slot'];

        $this->dispatch('startLoading');
        try {
            $assignment = WorkAssignment::where('user_id', $userId)
                ->where('slot', $slot)
                ->whereDate('timestamp', now()->toDateString())
                ->first();

            if ($assignment) {
                WorkAssignment::where('user_id', $userId)
                    ->whereBetween('slot', [$slot, $slot + $assignment->slots_occupied - 1])
                    ->whereDate('timestamp', now()->toDateString())
                    ->delete();
            }

            $this->loadLicenses();
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

    public function render()
    {
        return view('livewire.table-manager.work-assignment-table');
    }
}
