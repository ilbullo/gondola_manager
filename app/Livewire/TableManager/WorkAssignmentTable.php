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
     * 
     * @var array|null
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
     */
    public function mount(): void
    {
        $this->refreshTable();
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

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
     * Utilizza destroy() per maggiore sicurezza (gestione soft delete e primary key).
     */
    #[On('confirmRemoveAssignment')]
    public function removeAssignment(array $payload): void
    {
        $licenseTableId = $payload['licenseTableId'] ?? null;

        if (!$licenseTableId) {
            $this->errorMessage = 'Dati mancanti per rimuovere l\'assegnazione.';
            return;
        }

        $this->dispatch('closeWorkInfoModal');

        try {
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

    /**
     * Assegna un lavoro selezionato a uno slot della licenza indicata.
     * Esegue una validazione preliminare prima di delegare la logica al metodo saveAssignment().
     */
    public function assignWork(int $licenseTableId, int $slot): void
    {
        if (!$this->selectedWork || empty($this->selectedWork['value'])) {
            $this->errorMessage = 'Seleziona un lavoro valido dalla sidebar.';
            return;
        }

        $this->saveAssignment($licenseTableId, $slot, $this->selectedWork['slotsOccupied'] ?? 1);
    }

    /**
     * Apre la finestra informativa dettagliata su un lavoro presente in tabella.
     */
    public function openInfoBox($workId)
    {
        $this->dispatch('showWorkInfo', $workId);
    }

    /**
     * Salva fisicamente l'assegnazione di un lavoro a uno slot.
     * Include:
     * - Controllo conflitti (sovrapposizioni slot)
     * - Associazione agenzia (se applicabile)
     * - Creazione WorkAssignment
     * - Rinfresco della tabella
     */
    private function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied): void
    {
        try {
            // Associa l'agenzia se il lavoro è di tipo 'A'
            $agencyId = null;
            if ($this->selectedWork['value'] === 'A' && !empty($this->selectedWork['agencyName'])) {
                $agency = Agency::where('name', $this->selectedWork['agencyName'])->first();
                $agencyId = $agency?->id;
            }

            // Controllo sovrapposizioni slot
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

            // I lavori multi-slot diventano automaticamente esclusi
            $excluded = $slotsOccupied > 1 ? true : ($this->selectedWork['excluded'] ?? false);

            // Creazione record
            WorkAssignment::create([
                'license_table_id'  => $licenseTableId,
                'agency_id'         => $agencyId,
                'slot'              => $slot,
                'value'             => $this->selectedWork['value'],
                'amount'            => $this->selectedWork['amount'] ?? 90,
                'voucher'           => $this->selectedWork['voucher'] ?? null,
                'slots_occupied'    => $slotsOccupied,
                'excluded'          => $excluded,
                'shared_from_first' => $this->selectedWork['sharedFromFirst'] ?? false,
                'timestamp'         => now(),
            ]);

            $this->refreshTable();
            $this->errorMessage = '';
            $this->dispatch('workAssigned');

        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Errore durante l\'assegnazione del lavoro: ' . $e->getMessage();
        }
    }

    /**
     * Evento: rigenera la tabella completa delle licenze.
     * Utilizza LicenseResource per restituire una struttura uniforme lato Livewire.
     */
    #[On('refreshTableBoard')]
    public function refreshTable(): void
    {
        $licenses = LicenseTable::with([
            'user:id,license_number',
            'works' => fn($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code'),
        ])
        ->whereDate('date', today())
        ->orderBy('order')
        ->get();

        $this->licenses = LicenseResource::collection($licenses)->resolve();
    }

    /**
     * Genera il PDF della tabella delle assegnazioni.
     * I dati vengono inviati via Session alla PdfController@generate.
     */
    #[On('printWorksTable')]
    public function printTable(): void
    {
        $matrixData = collect($this->licenses)->map(function ($license) {
            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'worksMap'       => $license['worksMap'],
            ];
        })->sortBy('user.license_number')->values();

        Session::flash('pdf_generate', [
            'view'        => 'pdf.work-assignment-table',
            'data'        => [
                'matrix'      => $matrixData,
                'generatedBy' => Auth::user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'date'        => today()->format('d/m/Y'),
            ],
            'filename'    => 'tabella_assegnazione_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'landscape',
            'paper'       => 'a2',
        ]);

        $this->redirectRoute('generate.pdf');
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
