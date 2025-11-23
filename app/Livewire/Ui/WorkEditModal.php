<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\WorkAssignment; 
//use App\Enums\WorkType;

class WorkEditModal extends Component
{
    public bool $open = false;
    public $work = null;

    // Campi form
    public string $value = '';
    public ?string $agency_code = null;
    public ?string $voucher = null;
    public float $amount = 0.0;
    public bool $excluded = false;
    public bool $shared_from_first = false;

    #[On('openWorkEditForm')]
    public function open(int $workId): void
    {
        // Carica il lavoro dal DB usando solo l'ID (univoco!)
        $this->work = WorkAssignment::findOrFail($workId);

        // Popola i campi
        $this->value             = $this->work->value;
        $this->agency_code       = $this->work->agency_code;
        $this->voucher           = $this->work->voucher ?? $this->work->note;
        $this->amount            = (float) $this->work->amount;
        $this->excluded          = (bool) $this->work->excluded;
        $this->shared_from_first = (bool) $this->work->shared_from_first;

        $this->open = true;
    }

    public function save()
    {
        $this->validate([
           // 'value'       => 'required|in:A,X,N,P',
            'amount'      => 'required|numeric|min:0.01',
            'voucher'     => 'nullable|string|max:255',
            'agency_code' => 'nullable|string|max:50',
        ]);

        // Aggiorna direttamente il modello
        $this->work->update([
          //  'value'             => $this->value,
            'agency_code'       => $this->agency_code,
            'voucher'           => $this->voucher,
            'amount'            => $this->amount,
            'excluded'          => $this->excluded,
            'shared_from_first' => $this->shared_from_first,
        ]);

        // Notifica la tabella principale per aggiornare la vista
        $this->dispatch('work-updated', $this->work->refresh());

        $this->dispatch('toast', 'Lavoro modificato con successo!', 'success');

        $this->close();
    }

    public function close()
    {
        $this->open = false;
        $this->resetExcept('open');
        $this->work = null;
    }

    public function render()
    {
        return view('livewire.ui.work-edit-modal');
    }
}