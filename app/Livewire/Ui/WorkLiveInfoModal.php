<?php

namespace App\Livewire\Ui;

use App\Models\WorkAssignment;
use Carbon\Carbon;
use Livewire\Attributes\On; // cambia se il modello si chiama diversamente
use Livewire\Component;

class WorkLiveInfoModal extends Component
{
    public bool $open = false;

    public $work = null;

    // Campi per il form di modifica (retro della carta)
    public string $value = '';

    public ?string $agency_code = null;

    public ?string $voucher = null;

    public float $amount = 0.0;

    public bool $excluded = false;

    public bool $shared_from_first = false;

    #[On('showWorkInfo')]
    public function openModal($workId): void // Meglio ricevere solo l'ID
    {
        $this->work = WorkAssignment::findOrFail($workId);

        // Sincronizza i campi del form
        $this->value = $this->work->value;
        $this->agency_code = $this->work->agency_code;
        $this->voucher = $this->work->voucher ?? $this->work->note ?? '';
        $this->amount = (float) $this->work->amount;
        $this->excluded = (bool) $this->work->excluded;
        $this->shared_from_first = (bool) $this->work->shared_from_first;

        $this->open = true;

        // Forza Livewire a riconoscere il cambiamento (utile con Alpine)
        $this->dispatch('$refresh');
    }

    #[On('closeWorkInfoModal')]
    public function closeModal(): void
    {
        $this->open = false;
        $this->work = null;
        $this->reset(['value', 'agency_code', 'voucher', 'amount', 'excluded', 'shared_from_first']);
    }

    // Nel componente WorkLiveInfoModal.php
    public function getWorkDataProperty(): array
    {
        if (! $this->work) {
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
            'id' => $this->work->id,
            'value' => $this->work->value,
            'agency' => $this->work->agency,
            'agency_code' => $this->work->agency_code,
            'amount' => $this->work->amount,
            'voucher' => $this->work->voucher ?? $this->work->note ?? '',
            'excluded' => $this->work->excluded,
            'shared_from_first' => $this->work->shared_from_first,
            'time_elapsed' => $createdAt->diffForHumans(['parts' => 2, 'join' => ' e ']),
            'departure_time' => $createdAt->format('H:i'),
            'created_at' => $createdAt->format('d/m/Y H:i'),
        ];
    }

    public function save()
    {
        $this->validate([
            'value' => 'required|in:A,X,N,P',
            'amount' => 'required|numeric|min:0.01',
            'voucher' => 'nullable|string|max:255',
            'agency_code' => 'nullable|string|max:50',
        ]);

        $this->work->update([
            'value' => $this->value,
            'agency_code' => $this->agency_code,
            'voucher' => $this->voucher,
            'amount' => $this->amount,
            'excluded' => $this->excluded,
            'shared_from_first' => $this->shared_from_first,
        ]);

        // Aggiorna anche i campi visualizzati nel fronte
        $this->work->refresh();

        // Risincronizza i campi (importantissimo!)
    $this->fill($this->work->only([
        'value', 'agency_code', 'voucher', 'amount', 'excluded', 'shared_from_first'
    ]));
    $this->voucher = $this->work->voucher ?? $this->work->note ?? '';

    session()->flash('message', "Lavoro salvato con successo.");
    $this->dispatch('flip-to-front');
    $this->dispatch('work-updated');
    $this->dispatch('refreshTableBoard');
    }

    public function confirmDelete($id) {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo lavoro',
            'confirmEvent' => 'confirmRemoveAssignment',
            'cancelEvent'  => 'showWorkInfo('.$id.')',
            'payload'      => ['licenseTableId' => $id],
        ]);

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.ui.work-live-info-modal');
    }
}
