<?php

namespace App\Livewire\Ui;

use App\Models\WorkAssignment;
use App\Models\Agency;
use Carbon\Carbon;
use Livewire\Attributes\{On, Computed};
use Livewire\Component;

/**
 * Class WorkLiveInfoModal
 *
 * @package App\Livewire\Ui
 *
 * Gestore dell'ispezione dettagliata e della modifica "live" di un lavoro assegnato.
 * Il componente permette di visualizzare i metadati temporali (tempo trascorso, orario
 * di partenza) e di aggiornare i parametri economici o identificativi di un'assegnazione.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Transformation: Utilizza le Computed Properties per formattare i dati grezzi
 * del DB in informazioni leggibili per l'operatore (es. diffForHumans per il tempo trascorso).
 * 2. Bi-modal Interface: Supporta sia la visualizzazione informativa che la modifica
 * attiva (Editing) tramite il mapping esplicito dei campi del form.
 * 3. Atomic Updates: Centralizza la logica di salvataggio assicurando che la
 * sincronizzazione tra codici agenzia e ID agenzia sia gestita correttamente.
 * 4. Indirect Deletion: Facilita il processo di rimozione delegando l'azione distruttiva
 * al sistema di conferma globale (ModalConfirm).
 *
 * FLUSSO DATI:
 * [TableBoard] -> showWorkInfo(id) -> [WorkLiveInfoModal] -> Update ->
 * -> [refreshTableBoard] -> [WorkAssignmentTable]
 *
 * @property-read WorkAssignment|null $work Istanza del modello caricata via Computed.
 * @property-read array $workData Array formattato per il rendering della View.
 */

class WorkLiveInfoModal extends Component
{
    public bool $open = false;
    public ?int $workId = null;

    // Campi del form (Sincronizzati con wire:model)
    public string $value = '';
    public ?string $agency_code = null;
    public ?string $voucher = null;
    public float $amount = 0.0;
    public bool $excluded = false;
    public bool $shared_from_first = false;

    /**
     * SRP: Recupero del modello.
     */
    #[Computed]
    public function work(): ?WorkAssignment
    {
        return $this->workId ? WorkAssignment::find($this->workId) : null;
    }

    /**
     * SRP: Preparazione dati per la View.
     * Il componente si occupa solo di "passare" i dati pronti.
     */
    #[Computed]
    public function workData(): array
    {
        $work = $this->work;
        if (!$work) return [];

        $createdAt = Carbon::parse($work->created_at);

        return [
            'id'                => $work->id,
            'value'             => $work->value,
            'agency'            => $work->agency?->name,
            'agency_code'       => $work->agency_code,
            'amount'            => (float) $work->amount,
            'voucher'           => $work->voucher ?? $work->note ?? '',
            'time_elapsed'      => $createdAt->diffForHumans(['parts' => 1]),
            'departure_time'    => $createdAt->format('H:i'),
            'created_at'        => $createdAt->format('d/m/Y H:i'),
        ];
    }

    #[On('showWorkInfo')]
    public function openModal(int $workId): void
    {
        $this->workId = $workId;

        if ($work = $this->work) {
            // Mapping esplicito: il componente dichiara cosa gli serve.
            // Questo isola la UI dalla struttura del Database.
            $this->fill([
                'value'             => $work->value,
                'agency_code'       => $work->agency_code,
                'amount'            => (float) $work->amount,
                'excluded'          => (bool) $work->excluded,
                'shared_from_first' => (bool) $work->shared_from_first,
                'voucher'           => $work->voucher ?? $work->note ?? '',
            ]);

            $this->open = true;
        }
    }

    #[On('closeWorkInfoModal')]
    public function closeModal(): void
    {
        $this->open = false;
        $this->reset(['workId', 'value', 'agency_code', 'voucher', 'amount', 'excluded', 'shared_from_first']);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate([
            'value'        => 'required|in:A,X,N,P',
            'amount'       => 'required|numeric|min:0.01',
            'voucher'      => 'nullable|string|max:255',
            'agency_code'  => 'nullable|string|max:50',
            'excluded'     => 'boolean',
            'shared_from_first' => 'boolean',
        ]);

        $agency = Agency::findByCode($this->agency_code);

        $this->work->update([
            'value'             => $this->value,
            'agency_id'         => $agency?->id,
            'voucher'           => $this->voucher,
            'amount'            => $this->amount,
            'excluded'          => $this->excluded,
            'shared_from_first' => $this->shared_from_first,
        ]);

        $this->dispatch('flip-to-front');
        $this->dispatch('work-updated');
        $this->dispatch('refreshTableBoard');

        session()->flash('message', "Lavoro aggiornato.");
    }

    public function confirmDelete(): void
    {
        if (!$this->workId) return;

        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo lavoro?',
            'confirmEvent' => 'confirmRemoveAssignment',
            'payload'      => ['licenseTableId' => $this->workId],
        ]);

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.ui.work-live-info-modal');
    }
}
