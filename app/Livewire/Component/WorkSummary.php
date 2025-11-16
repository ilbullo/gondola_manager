<?php

namespace App\Livewire\Component;

use App\Models\WorkAssignment;
use Livewire\Component;

class WorkSummary extends Component
{
    public $counts = [
        'N'  => 0,
        'X'  => 0,
        'A'  => 0,
        'P'  => 0,
    ];
    public $total = 0;

    // Aggiungi listeners per eventi dalla tabella
    protected $listeners = [
        'workAssigned' => 'loadCounts',  // Emesso dopo assegnazione lavoro
        'confirmRemoveAssignment' => 'loadCounts',   // Emesso dopo rimozione lavoro
        // Aggiungi altri eventi se necessario, es. 'tableReset' => 'loadCounts'
    ];

    public function mount()
    {
        $this->loadCounts();
    }

    public function loadCounts()
    {
        // Query per contare i tipi di lavoro
        $results = WorkAssignment::groupBy('value')
            ->selectRaw('value, COUNT(*) as count')
            ->whereIn('value', array_keys($this->counts))
            ->pluck('count', 'value')
            ->toArray();

        // Aggiorna i conteggi
        foreach ($this->counts as $type => $value) {
            $this->counts[$type] = $results[$type] ?? 0;
        }
        // Calcola il totale
        $this->total = array_sum($this->counts);
    }

    public function render()
    {
        return view('livewire.component.work-summary');
    }
}