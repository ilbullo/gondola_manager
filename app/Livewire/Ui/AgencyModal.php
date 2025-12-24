<?php

namespace App\Livewire\Ui;

use App\Models\Agency;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;

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