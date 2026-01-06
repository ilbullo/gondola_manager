<?php

namespace App\Livewire\TableManager;

use App\Http\Resources\LicenseResource;
use App\Services\WorkAssignmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Traits\HasPdfPreview;

/**
 * Class WorkAssignmentTable
 *
 *
 * @property array $licenses Struttura dati trasformata (via Resource) che rappresenta le righe della tabella.
 * @property array|null $selectedWork Snapshot del lavoro attualmente "caricato" sul cursore dell'utente.
 */
class WorkAssignmentTable extends Component
{

    use HasPdfPreview;
    /**
     * Elenco delle licenze con i relativi lavori assegnati.
     * Viene popolato tramite refreshTable().
     *
     * @var array<int, mixed>
     */
    public array $licenses = [];

    /**
     * Lavoro selezionato dalla sidebar (es. contanti, nolo, agenzia, ecc.).
     * Contiene tutti i dati utili per una futura assegnazione.
     */
    public ?array $selectedWork = null;

    /**
     * Messaggio di errore da mostrare all’utente in caso di problemi (conflitti, dati mancanti, ecc.).
     */
    public string $errorMessage = '';

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializzazione del componente.
     * Al mount viene popolata la tabella completa delle licenze e dei loro lavori.
     * Livewire gestisce automaticamente il wire:loading, evitando stati incoerenti.
     * Usiamo il Service per popolare i dati al mount.
     */
    public function mount(WorkAssignmentService $service): void
    {
        $this->licenses = $service->refreshTable();
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

    /**
     * Evento: cicla il turno di una licenza tra full, morning e afternoon.
     */
    public function cycleTurn(int $licenseId, WorkAssignmentService $service): void
    {
        $service->cycleLicenseTurn($licenseId);
        $this->refreshTable($service);
    }

    /**
     * Attiva disattiva il valore di only_cash_works
     */
    public function toggleOnlyCash(int $licenseId, WorkAssignmentService $service): void
    {
        $service->toggleLicenseCashOnly($licenseId);
        $this->refreshTable($service);
    }

    /**
     * Evento Livewire: aggiornamento del lavoro selezionato dalla sidebar.
     * Resetta eventuali messaggi di errore precedenti.
     */
    #[On('workSelected')]
    public function handleWorkSelected(?array $work): void
    {
        $this->selectedWork = $work;
        $this->errorMessage = '';
    }

    /**
     * Apre il modal per modificare una licenza specifica.
     * Utilizza il sistema di modals centralizzato tramite eventi Livewire.
     */
    public function openEditLicenseModal($id)
    {
        $this->dispatch('openEditLicense', ['id' => $id]);
    }

    /**
     * Evento: rimuove un lavoro assegnato a una licenza, dopo conferma dell’utente.
     */
    #[On('confirmRemoveAssignment')]
    public function removeAssignment(array $payload, WorkAssignmentService $service): void
    {
        $id = $payload['licenseTableId'] ?? null;

        if (! $id) {
            $this->errorMessage = 'ID assegnazione mancante.';
            $this->dispatch('notify', message: $this->errorMessage, type: 'error'); // Aggiungi questo

            return;
        }

        try {
            $service->deleteAssignment($id);
            $this->refreshTable($service);
            $this->dispatch('notify-success', ['message' => 'Lavoro rimosso correttamente']);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            // Assicurati che l'errore venga notificato al browser/test
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Metodo pubblico che delega al service il salvataggio.
     */

    // In WorkAssignmentTable.php

    public function assignWork(int $licenseTableId, int $slot, WorkAssignmentService $service): void
    {
        if (! $this->selectedWork || empty($this->selectedWork['value'])) {
            $this->dispatch('notify', ['message' => 'Seleziona un lavoro dalla sidebar', 'type' => 'error']);

            return;
        }

        try {
            // Il service si occupa della persistenza su DB
            $service->saveAssignment(
                $licenseTableId,
                $slot,
                $this->selectedWork['slotsOccupied'] ?? 1,
                $this->selectedWork
            );

            $this->refreshTable($service);

            // Reset dello stato locale
            $this->errorMessage = '';

            // Notifichiamo alla sidebar che il lavoro è stato usato (se necessario)
            $this->dispatch('workAssigned');
            $this->dispatch('notify-success', ['message' => 'Lavoro assegnato con successo']);

        } catch (\Exception $e) {
            // Gestione errori centralizzata
            $this->errorMessage = $e->getMessage();
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    /**
     * Apre la finestra informativa dettagliata su un lavoro presente in tabella.
     */
    public function openInfoBox($workId)
    {
        $this->dispatch('showWorkInfo', $workId);
    }

    /**
     * Evento: rigenera la tabella completa delle licenze.
     * Utilizza LicenseResource per restituire una struttura uniforme lato Livewire.
     */
    #[On('refreshTableBoard')]
    public function refreshTable(WorkAssignmentService $service): void
    {
        $this->licenses = $service->refreshTable();
        // evento per work summary
        $this->dispatch('matrix-updated', licenses: $this->licenses);
    }

    /**
     * Genera il PDF della tabella delle assegnazioni.
     * I dati vengono inviati via Session alla PdfController@generate.
     */
    #[On('printWorksTable')]
    public function printTable(WorkAssignmentService $service): void
    {
        // Delega la preparazione dei dati al service
        $matrixData = $service->preparePdfData($this->licenses);

        // 2. Configurazione per l'anteprima professionale
        $this->openPdfPreview(
            view: 'pdf.work-assignment-table',
            data: [
                'matrix'      => $matrixData,
                'generatedBy' => Auth::user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'date'        => today()->format('d/m/Y'),
            ],
            orientation: 'landscape',
            paper: 'a4' // Supporto per il grande formato
        );
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Rende la vista principale della tabella delle assegnazioni.
     */
    public function render()
    {
        return view('livewire.table-manager.work-assignment-table');
    }
}
