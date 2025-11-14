<?php

namespace App\Livewire\TableManager;


use Livewire\Component;
use App\Models\User;
use App\Models\LicenseTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class LicenseManager extends Component
{
    public $availableUsers = [];
    public $selectedUsers = [];
    public $errorMessage = '';

    public function mount()
    {
        // Carica tutti gli utenti disponibili (escludendo quelli già selezionati)
        $this->loadAvailableUsers();
        // Carica gli utenti selezionati dalla tabella license_table
        $this->loadSelectedUsers();
    }

    public function loadAvailableUsers()
    {
        // Carica gli utenti che non sono nella tabella license_table per la data odierna
        $this->availableUsers = User::whereDoesntHave('atWork', function ($query) {
            $query->whereDate('date', now()->toDateString());
        })->orderBy('license_number','ASC')->get()->toArray();
    }

    public function loadSelectedUsers()
    {
        // Carica gli utenti selezionati per la data odierna, ordinati per 'order'
        $this->selectedUsers = LicenseTable::whereDate('date', now()->toDateString())
            ->with('user')
            ->orderBy('order')
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'user_id' => $license->user_id,
                    'order' => $license->order,
                    'user' => $license->user ? [
                        'id' => $license->user->id,
                        'name' => $license->user->name,
                        'surname' => $license->user->surname ?? '',
                        'license' => $license->user->license_number,
                    ] : null,
                ];
            })
            ->filter(function ($item) {
                return !is_null($item['user']);
            })
            ->toArray();
    }

    public function selectUser($userId)
    {
        $this->dispatch('startLoading');
        // Trova l'utente selezionato
        $user = User::findOrFail($userId);

        // Determina il prossimo ordine
        $maxOrder = LicenseTable::whereDate('date', now()->toDateString())->max('order') ?? 0;

        // Crea un record nella tabella license_table
        LicenseTable::create([
            'user_id' => $user->id,
            'date' => now(),
            'order' => $maxOrder + 1,
        ]);

        // Ricarica gli utenti disponibili e selezionati
        $this->loadAvailableUsers();
        $this->loadSelectedUsers();
        $this->dispatch('stopLoading');
    }

    public function removeUser($licenseTableId)
    {
        $this->dispatch('startLoading');
        // Rimuovi il record dalla tabella license_table
        $license = LicenseTable::findOrFail($licenseTableId);
        $license->delete();

        // Ricarica gli utenti disponibili e selezionati
        $this->loadAvailableUsers();
        $this->loadSelectedUsers();
        $this->dispatch('stopLoading');
    }

    public function updateOrder($orderedIds)
    {
        $this->dispatch('startLoading');
        // Aggiorna l'ordine dei record nella tabella license_table
        foreach ($orderedIds as $index => $item) {
            LicenseTable::where('id', $item['value'])->update(['order' => $index + 1]);
        }

        // Ricarica gli utenti selezionati per riflettere il nuovo ordine
        $this->loadSelectedUsers();
        $this->dispatch('stopLoading');
        session()->flash('success', 'Ordine aggiornato con successo!');
    }

    public function confirm()
    {
        $this->dispatch('startLoading');
        // Valida che ci sia almeno un utente selezionato
        if (empty($this->selectedUsers)) {
            $this->errorMessage = 'Seleziona almeno un utente prima di confermare.';
            $this->dispatch('stopLoading');
            return;
        }

        // Resetta il messaggio di errore
        $this->errorMessage = '';

        // Per ora, logga la conferma (puoi implementare il passaggio alla sezione successiva qui)
        \Log::info('Conferma selezione utenti', ['selectedUsers' => $this->selectedUsers]);

        // Puoi aggiungere un redirect o altre azioni qui in futuro
        session()->flash('success', 'Selezione confermata con successo!');
        $this->dispatch('stopLoading');
    }

    public function render()
    {
        return view('livewire.table-manager.license-manager');
    }
}
