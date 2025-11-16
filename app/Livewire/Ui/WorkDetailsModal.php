<?php

namespace App\Livewire\Ui;

use Livewire\Component;

class WorkDetailsModal extends Component
{
    public $amount = 90;
    public $slotsOccupied = 1;
    public $excluded = false;
    public $isOpen = false;

    protected $listeners = [
        'openWorkDetailsModal' => 'openModal',
        'workSelected' => 'updateFromSelectedWork',
    ];

    public function openModal()
    {
        \Log::info('WorkDetailsModal: Opening modal');
        $this->isOpen = true;
    }

    public function closeModal()
    {
        \Log::info('WorkDetailsModal: Closing modal');
        $this->isOpen = false;
        $this->reset(['amount', 'slotsOccupied', 'excluded']);
        $this->amount = 90;
        $this->slotsOccupied = 1;
    }

    public function updateFromSelectedWork($work)
    {
        \Log::info('WorkDetailsModal: Updating from workSelected', $work);
        $this->amount = $work['amount'] ?? 90;
        $this->slotsOccupied = $work['slotsOccupied'] ?? 1;
        $this->excluded = $work['excluded'] ?? false;
    }

    public function save()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'slotsOccupied' => 'required|integer|in:1,2',
            'excluded' => 'boolean',
        ]);

        \Log::info('WorkDetailsModal: Saving work details', [
            'amount' => $this->amount,
            'slotsOccupied' => $this->slotsOccupied,
            'excluded' => $this->excluded,
        ]);

        // Emetti evento per aggiornare Sidebar
        $this->dispatch('updateWorkDetails', [
            'amount' => $this->amount,
            'slotsOccupied' => $this->slotsOccupied,
            'excluded' => $this->excluded,
        ]);

        $this->closeModal();
    }

    public function resetForm()
    {
        \Log::info('WorkDetailsModal: Resetting form fields');
        $this->amount = 90;
        $this->slotsOccupied = 1;
        $this->excluded = false;

        \Log::info('WorkDetailsModal: Form fields reset', [
            'amount' => $this->amount,
            'slotsOccupied' => $this->slotsOccupied,
            'excluded' => $this->excluded,
        ]);

    }

    public function render()
    {
        return view('livewire.ui.work-details-modal');
    }
}