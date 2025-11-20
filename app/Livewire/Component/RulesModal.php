<?php

namespace App\Livewire\Component;

use Livewire\Component;

class RulesModal extends Component
{
    // Proprietà pubblica per controllare lo stato del modale.
    public bool $isOpen = false;

    // Metodo chiamato per aprire il modale (usato dal link/bottone).
    public function openModal(): void
    {
        $this->isOpen = true;
    }

    // Metodo chiamato per chiudere il modale (usato dai bottoni o dallo sfondo).
    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        // Renderizza la view che contiene il contenuto e la struttura del modale.
        return view('livewire.component.rules-modal');
    }
}