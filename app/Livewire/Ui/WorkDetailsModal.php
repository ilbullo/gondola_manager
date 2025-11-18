<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;

class WorkDetailsModal extends Component
{
    // === Stato del modale ===
    public bool $isOpen = false;

    // === Dati del form ===
    public float|int $amount = 90;
    public int $slotsOccupied = 1;
    public bool $excluded = false;

    // === Regole di validazione (best practice Livewire 3) ===
    protected function rules(): array
    {
        return [
            'amount'        => 'required|numeric|min:0',
            'slotsOccupied' => 'required|integer|in:1,2,3,4',
            'excluded'      => 'boolean',
        ];
    }

    // === Listener moderni con attributi (Livewire 3+) ===
    #[On('openWorkDetailsModal')]
    public function openModal(): void
    {
        $this->isOpen = true;
        $this->resetErrorBag(); // Pulisce eventuali errori precedenti
    }

    #[On('workSelected')]
    public function updateFromSelectedWork(array $work): void
    {
        $this->amount        = $work['amount'] ?? 90;
        $this->slotsOccupied = $work['slotsOccupied'] ?? 1;
        $this->excluded      = $work['excluded'] ?? false;
    }

    public function save(): void
    {
        $this->validate();

        $this->dispatch('updateWorkDetails', [
            'amount'        => $this->amount,
            'slotsOccupied' => $this->slotsOccupied,
            'excluded'      => $this->excluded,
        ]);

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    // === Metodo privato per il reset (evita duplicazione) ===
    private function resetForm(): void
    {
        $this->amount        = 90;
        $this->slotsOccupied = 1;
        $this->excluded      = false;
    }

    // === Opzionale: reset completo quando il componente viene mountato ===
    public function mount(): void
    {
        $this->resetForm();
    }

    // === Render ===
    public function render()
    {
        return view('livewire.ui.work-details-modal');
    }
}