<?php
namespace App\Livewire\Component;

use App\Enums\WorkType;
use Livewire\Component;
use Livewire\Attributes\On;

class WorkSummary extends Component
{
    public array $counts = [];
    public int $total = 0;

    /**
     * Riceve le licenze iniziali e calcola subito i totali
     */
    public function mount(array $licenses = []): void
    {
        $this->updateFromMatrix($licenses);
    }

    /**
     * Reagisce all'evento della tabella
     */
    #[On('matrix-updated')]
    public function updateFromMatrix(array $licenses = []): void
    {
        $this->resetCounts();

        // Se non ci sono licenze (es. al mount vuoto), fermati
        if (empty($licenses)) {
            $this->total = 0;
            return;
        }

        foreach ($licenses as $license) {
            // Supporto sia per array che per oggetti Model
            $works = is_array($license) ? ($license['worksMap'] ?? []) : ($license->worksMap ?? []);

            foreach ($works as $work) {
                // Supporto per dati array o oggetti
                $val = is_array($work) ? ($work['value'] ?? null) : ($work->value ?? null);

                if ($val && isset($this->counts[$val])) {
                    $this->counts[$val]++;
                }
            }
        }

        $this->total = array_sum($this->counts);
    }

    private function resetCounts(): void
    {
        $this->counts = collect(WorkType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => 0])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.component.work-summary');
    }
}
