<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, User};
use Livewire\Component;
use Livewire\Attributes\{On, Computed};

class LicenseManager extends Component
{
    public string $search = '';

    // Non abbiamo più bisogno di mount() o refreshData() manuali per le liste
    // perché usiamo le Computed Properties (#[Computed])

    #[Computed]
    public function availableUsers()
    {
        $assignedIds = LicenseTable::whereDate('date', today())->pluck('user_id');

        return User::whereNotIn('id', $assignedIds)
            ->when($this->search, function ($query) {
                $query->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('license_number', 'like', "%{$this->search}%"));
            })
            ->orderBy('license_number')
            ->get();
    }

    #[Computed]
    public function selectedUsers()
    {
        return LicenseTable::whereDate('date', today())
            ->with('user')
            ->orderBy('order')
            ->get();
    }

    public function selectUser(int $userId): void
    {
        // 1. Prevenzione: Verifichiamo se l'utente è già presente per oggi
        // Questo blocca inserimenti duplicati anche in caso di click multipli
        $exists = LicenseTable::where('user_id', $userId)
            ->whereDate('date', today())
            ->exists();

        if ($exists) {
            $this->notify("L'utente è già in tabella.", 'warning');
            return;
        }

        // 2. Esecuzione: Creiamo la riga solo se non esiste
        LicenseTable::create([
            'user_id' => $userId,
            'date'    => today(),
            'order'   => (LicenseTable::whereDate('date', today())->max('order') ?? 0) + 1,
        ]);

        $this->notify("Licenza aggiunta con successo.");
    }

    public function removeUser(int $id): void
    {
        LicenseTable::destroy($id);
        $this->notify("Rimosso dall'ordine.", 'warning');
    }

    public function moveUp(int $id) { LicenseTable::swap($id, 'up'); }
    public function moveDown(int $id) { LicenseTable::swap($id, 'down'); }

    #[On('confirmResetTable')]
    public function performReset(): void
    {
        LicenseTable::whereDate('date', today())->delete();
        $this->notify('Tabella resettata.', 'error');
    }

    public function resetTable(): void
    {
        $this->dispatch('openConfirmModal', [
            'message' => 'Svuotare l\'ordine di servizio?',
            'confirmEvent' => 'confirmResetTable'
        ]);
    }

    /**
     * Conferma l'ordine di servizio e procede alla fase successiva.
     */
    public function confirm(): void
    {
        // Verifichiamo che ci sia almeno un utente selezionato (sicurezza extra)
        if ($this->selectedUsers->isEmpty()) {
            $this->notify("Seleziona almeno una licenza prima di confermare.", 'error');
            return;
        }

        // Qui puoi inserire la logica di business per "iniziare il lavoro"
        // Esempio: Cambiare lo stato della giornata, loggare l'inizio, etc.
        
        // Inviamo l'evento al sistema (utile per altri componenti)
        $this->dispatch('licensesConfirmed');

        // Notifica di successo
        $this->notify("Ordine di servizio confermato. Buon lavoro!", 'success', 'TURNO AVVIATO');
        
        // Opzionale: reindirizza alla dashboard o a una visualizzazione di sola lettura
        // return redirect()->route('dashboard');
    }

    private function notify($msg, $type = 'success')
    {
        $this->dispatch('notify', message: $msg, type: $type);
    }

    public function render() {
        return view('livewire.table-manager.license-manager');
    }
}