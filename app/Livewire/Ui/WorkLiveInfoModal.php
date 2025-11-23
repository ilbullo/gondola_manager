<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class WorkLiveInfoModal extends Component
{
    public $work = [];   // Riceve tutto il lavoro selezionato
    public bool $open = false;

    #[On('showWorkInfo')]
    public function openModal($work,$slot) : void
    {
       $this->work = array_merge($work, ["slot" => $slot]);
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->work = [];
    }

    public function getFormattedWorkProperty(): array
    {
        if (empty($this->work) || !isset($this->work['created_at'])) {
            return $this->work; // Restituisce i dati base se il tempo non è disponibile
        }

        $createdAt = Carbon::parse($this->work['created_at']);
        
        // Calcolo del tempo trascorso ("X tempo fa") - L'informazione più importante
        $timeElapsed = $createdAt->diffForHumans(Carbon::now(), true); 
        
        // Formattazione dell'ora e della data di partenza
        $departureTime = $createdAt->format('H:i:s');
        $departureDate = $createdAt->format('d/m/Y');
        // Unisce i dati originali con le nuove proprietà calcolate
        return array_merge($this->work, [
            'departure_time' => $departureTime,
            'departure_date' => $departureDate,
            'time_elapsed'   => $timeElapsed,
        ]);
    }

    // Placeholder per la funzione di modifica
    public function editWork(int $workId): void
    {
        // Qui andrebbe la logica per aprire un form di modifica,
        // ad esempio emettendo un evento Livewire:
        // $this->dispatch('openWorkEditForm', $workId);
        // Per ora chiudiamo la modale:
        $this->closeModal();
    }

    public function openConfirmRemove(int $licenseTableId, int $slot): void
    {
        $this->closeModal();
        $this->dispatch('openConfirmModal', [
            'message'      => 'Vuoi rimuovere il lavoro da questa cella?',
            'confirmEvent' => 'confirmRemoveAssignment',
            'payload'      => compact('licenseTableId','slot'),
        ]);
    }

    public function render()
    {
        return view('livewire.ui.work-live-info-modal');
    }
}