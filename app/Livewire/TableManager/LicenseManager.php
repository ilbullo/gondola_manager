<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, User};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class LicenseManager extends Component
{
    /** @var array<int, array{id: int, user_id: int, name: string, surname: string, license: string|null}> */
    public array $availableUsers = [];

    /** @var array<int, array{id: int, user_id: int, order: int, user: array{id: int, name: string, surname: string, license: string|null}}> */
    public array $selectedUsers = [];

    public string $errorMessage = '';

    // ===================================================================
    // Lifecycle
    // ===================================================================

    public function mount(): void
    {
        $this->refreshData();
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

    public function selectUser(int $userId): void
    {
        $this->dispatch('toggleLoading', true);

        $user = User::findOrFail($userId);

        $nextOrder = $this->getNextOrder();

        LicenseTable::create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => $nextOrder,
        ]);

        $this->refreshData();
        $this->dispatch('toggleLoading', false);
    }

    public function removeUser(int $licenseTableId): void
    {
        $this->dispatch('toggleLoading', true);

        LicenseTable::findOrFail($licenseTableId)->delete();

        $this->refreshData();
        $this->dispatch('toggleLoading', false);
    }

    /** Riceve l'array da Alpine (Livewire sortable) */
    public function updateOrder(array $orderedIds): void
    {
        $this->dispatch('toggleLoading', true);

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $item) {
                LicenseTable::where('id', $item['value'])
                    ->update(['order' => $index + 1]);
            }
        });

        $this->loadSelectedUsers();
        $this->dispatch('toggleLoading', false);

        session()->flash('success', 'Ordine aggiornato con successo!');
    }

    public function confirm(): void
    {
        $this->dispatch('toggleLoading', true);

        if (empty($this->selectedUsers)) {
            $this->errorMessage = 'Seleziona almeno un utente prima di confermare.';
            $this->dispatch('toggleLoading', false);
            return;
        }

        $this->errorMessage = '';

        session()->flash('success', 'Selezione confermata con successo!');
        $this->dispatch('confirmLicenses'); // Va al TableManager

        $this->dispatch('toggleLoading', false);
    }

    // ===================================================================
    // Private Helpers
    // ===================================================================

    private function refreshData(): void
    {
        $this->loadAvailableUsers();
        $this->loadSelectedUsers();
    }

    private function loadAvailableUsers(): void
    {

        // 1. Ottieni gli ID degli utenti già assegnati
        $assignedUserIds = LicenseTable::whereDate('date', today())
        ->pluck('user_id');

        // 2. Query ottimizzata
        $this->availableUsers = User::whereNotIn('id', $assignedUserIds)
            ->orderBy('license_number')
            ->get()
            ->map(fn($user) => [
                'id'                => $user->id,
                'name'              => $user->name,
                'surname'           => $user->surname ?? '',
                'license_number'    => $user->license_number,
                'full_name'         => trim("{$user->name} {$user->surname}"),
            ])
            ->toArray();
    }

    private function loadSelectedUsers(): void
    {
        $this->selectedUsers = LicenseTable::whereDate('date', today())
            ->with('user')
            ->orderBy('order')
            ->get()
            ->filter(fn($lt) => $lt->user !== null)
            ->map(fn($lt) => [
                'id'       => $lt->id,
                'user_id'  => $lt->user_id,
                'order'    => $lt->order,
                'user'     => [
                    'id'       => $lt->user->id,
                    'name'     => $lt->user->name,
                    'surname'  => $lt->user->surname ?? '',
                    'license'  => $lt->user->license_number,
                    'full_name' => trim("{$lt->user->name} " . ($lt->user->surname ?? '')),
                ],
            ])
            ->values()
            ->toArray();
    }

    private function getNextOrder(): int
    {
        return LicenseTable::whereDate('date', today())->max('order') + 1 ?? 1;
    }

    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.table-manager.license-manager');
    }
}
