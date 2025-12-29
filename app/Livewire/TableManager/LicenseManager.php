<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, User};
use Livewire\Component;
use Livewire\Attributes\{On, Computed};

/**
 * Class LicenseManager
 *
 * @package App\Livewire\TableManager
 *
 * Gestisce l'allestimento dell'Ordine di Servizio giornaliero.
 * Permette la selezione dei conducenti disponibili, la definizione dell'ordine di uscita
 * e il riordino dinamico delle licenze prima dell'inizio delle attività operative.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. State Orchestration: Gestisce due flussi di dati paralleli (utenti disponibili vs utenti selezionati)
 * utilizzando Computed Properties per garantire performance elevate e dati sempre sincronizzati.
 * 2. Order Management: Implementa la logica di ordinamento sequenziale e lo swapping delle posizioni
 * per riflettere la gerarchia dei turni nel database.
 * 3. Double-Booking Prevention: Integra controlli di integrità per evitare che un utente
 * venga assegnato più volte nella stessa data operativa.
 * 4. Workflow Transition: Funge da "Gatekeeper" per il passaggio dalla fase di configurazione
 * alla fase di assegnazione lavori tramite l'azione di conferma definitiva.
 *
 * OTTIMIZZAZIONE:
 * - L'uso di #[Computed] riduce il carico sul server memorizzando i risultati delle query
 * all'interno dello stesso ciclo di richiesta (request lifecycle).
 *
 * @property-read \Illuminate\Support\Collection $availableUsers Utenti non ancora assegnati al turno.
 * @property-read \Illuminate\Support\Collection $selectedUsers Utenti attualmente inseriti nell'ordine di servizio.
 */

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
//        usleep(1000000);
        return view('livewire.table-manager.license-manager');
    }
}
