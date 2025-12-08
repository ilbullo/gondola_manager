<?php

namespace App\Livewire\Ui;

use App\Models\WorkAssignment;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkLiveInfoModal extends Component
{
    /**
     * Indica se il modale è attualmente aperto.
     * @var bool
     */
    public bool $open = false;

    /**
     * Istanza del lavoro selezionato.
     * @var WorkAssignment|null
     */
    public $work = null;

    // === Campi del form (retro della carta) ===

    /** @var string Codice/lettera del lavoro (A, X, N, P). */
    public string $value = '';

    /** @var string|null Codice identificativo dell'agenzia. */
    public ?string $agency_code = null;

    /** @var string|null Codice voucher o nota associata. */
    public ?string $voucher = null;

    /** @var float Importo economico del lavoro. */
    public float $amount = 0.0;

    /** @var bool Indica se il lavoro è escluso dalla lavorazione. */
    public bool $excluded = false;

    /** @var bool Indica se il lavoro parte come condiviso dal primo. */
    public bool $shared_from_first = false;


    // === Apertura modale ===

    /**
     * Apre il modale caricando i dati del lavoro richiesto.
     *
     * @param int $workId  ID del lavoro da mostrare
     */
    #[On('showWorkInfo')]
    public function openModal($workId): void
    {
        $this->work = WorkAssignment::findOrFail($workId);
        // Sincronizza i campi del form con l'istanza del modello
        $this->value              = $this->work->value;
        $this->agency_code        = $this->work->agency_code;
        $this->voucher            = $this->work->voucher ?? $this->work->note ?? '';
        $this->amount             = (float) $this->work->amount;
        $this->excluded           = (bool) $this->work->excluded;
        $this->shared_from_first  = (bool) $this->work->shared_from_first;

        $this->open = true;

        // Forza un refresh (utile quando c'è Alpine o animazioni legate allo stato)
        $this->dispatch('$refresh');
    }


    // === Chiusura modale ===

    /**
     * Chiude il modale e resetta i campi del form.
     */
    #[On('closeWorkInfoModal')]
    public function closeModal(): void
    {
        $this->open = false;
        $this->work = null;

        // Reset dei soli campi modificabili
        $this->reset([
            'value',
            'agency_code',
            'voucher',
            'amount',
            'excluded',
            'shared_from_first'
        ]);
    }


    // === Proprietà computata per la view ===

    /**
     * Restituisce i dati del lavoro in un array pronto per la view.
     * Utile quando il modale mostra informazioni "live".
     *
     * @return array<string,mixed>
     */
    public function getWorkDataProperty(): array
    {
        if (! $this->work) {
            // Stato di default quando nessun lavoro è selezionato
            return [
                'id' => null,
                'value' => '',
                'agency' => '',
                'agency_code' => '',
                'amount' => 0.0,
                'voucher' => '',
                'excluded' => false,
                'shared_from_first' => false,
                'time_elapsed' => '',
                'departure_time' => '',
                'created_at' => '',
            ];
        }

        $createdAt = Carbon::parse($this->work->created_at);

        return [
            'id'                => $this->work->id,
            'value'             => $this->work->value,
            'agency'            => $this->work->agency,
            'agency_code'       => $this->work->agency_code,
            'amount'            => $this->work->amount,
            'voucher'           => $this->work->voucher ?? $this->work->note ?? '',
            'excluded'          => $this->work->excluded,
            'shared_from_first' => $this->work->shared_from_first,
            'time_elapsed'      => $createdAt->diffForHumans(['parts' => 2, 'join' => ' e ']),
            'departure_time'    => $createdAt->format('H:i'),
            'created_at'        => $createdAt->format('d/m/Y H:i'),
        ];
    }


    // === Salvataggio ===

    /**
     * Valida e salva i dati aggiornati del lavoro.
     * Dopo il salvataggio:
     *  - aggiorna il modello
     *  - risincronizza i campi del form
     *  - emette gli eventi verso il front-end
     */
    public function save()
    {
        $this->validate([
            'value'        => 'required|in:A,X,N,P',
            'amount'       => 'required|numeric|min:0.01',
            'voucher'      => 'nullable|string|max:255',
            'agency_code'  => 'nullable|string|max:50',
        ]);
        // Aggiorna il modello

        //verifico l'agenzia dal
        if ($this->agency_code) {
            $id = \App\Models\Agency::select(['id'])->where('code',$this->agency_code)->get()->first()->id;
        }
        $this->work->update([
            'value'             => $this->value,
            'agency_id'         => $id ?? null,
            'voucher'           => $this->voucher,
            'amount'            => $this->amount,
            'excluded'          => $this->excluded,
            'shared_from_first' => $this->shared_from_first,
        ]);

        // Aggiorna l'istanza con i valori effettivi salvati nel DB
        $this->work->refresh();

        // Risincronizza il form con i dati aggiornati
        $this->fill($this->work->only([
            'value', 'agency_code', 'voucher', 'amount', 'excluded', 'shared_from_first'
        ]));

        // Fall-back per note/voucher
        $this->voucher = $this->work->voucher ?? $this->work->note ?? '';

        session()->flash('message', "Lavoro salvato con successo.");

        // Eventi utili per UI/animazioni
        $this->dispatch('flip-to-front');
        $this->dispatch('work-updated');
        $this->dispatch('refreshTableBoard');
    }


    // === Eliminazione ===

    /**
     * Chiede conferma eliminazione usando il modal centrale conferme.
     *
     * @param int $id ID del lavoro da eliminare
     */
    public function confirmDelete($id)
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo lavoro',
            'confirmEvent' => 'confirmRemoveAssignment',
            'cancelEvent'  => 'showWorkInfo('.$id.')',
            'payload'      => ['licenseTableId' => $id],
        ]);

        $this->closeModal();
    }


    /**
     * Renderizza la view Livewire del modale.
     */
    public function render()
    {
        return view('livewire.ui.work-live-info-modal');
    }
}
