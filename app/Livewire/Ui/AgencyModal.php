<?php

namespace App\Livewire\Ui;

use App\Models\Agency;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;

/**
 * Class AgencyModal
 *
 * @package App\Livewire\Ui
 *
 * Gestisce l'interfaccia di selezione rapida delle agenzie.
 * Il componente agisce come un selettore (Picker) disaccoppiato che fornisce
 * i dati dell'anagrafica al componente Sidebar quando richiesto.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Interface Segregation: Separa la selezione dell'agenzia dalla gestione del lavoro,
 * mantenendo la Sidebar snella e focalizzata sulla configurazione dei parametri.
 * 2. Performance & Caching: Implementa una strategia di "Lazy Loading" tramite #[Computed]
 * e utilizza il caching (agencies_list) per minimizzare le query al database.
 * 3. Event-Driven Communication: Comunica la scelta dell'utente tramite dispatching
 * di eventi ('agencySelected'), permettendo a qualsiasi componente in ascolto di reagire.
 * 4. UX State Management: Controlla la propria visibilità tramite toggle reattivi,
 * garantendo una pulizia automatica dei messaggi di errore alla chiusura.
 *
 * FLUSSO DATI:
 * [Sidebar] -> toggleAgencyModal(true) -> [AgencyModal] -> selectAgency(id) -> [Sidebar]
 */

class AgencyModal extends Component
{
    public bool $show = false;

    #[Computed]
    public function agencies(): Collection
    {
        if (!$this->show) {
            return collect();
        }

        // Recuperiamo dalla cache.
        // Se la cache restituisce un array, lo trasformiamo in Collection.
        $data = cache()->remember('agencies_list', 3600, function() {
            return Agency::toBase()->get(['id', 'name', 'code']);
        });

        return collect($data);
    }

    #[On('toggleAgencyModal')]
    public function toggle(bool $visible): void
    {
        $this->show = $visible;
        if (!$visible) {
            $this->resetErrorBag();
        }
    }

    public function selectAgency(int $id): void
    {
        $this->dispatch('agencySelected', agencyId: $id);
        $this->close();
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.ui.agency-modal');
    }
}
