<?php

namespace App\Livewire\Component;

use App\Models\WorkAssignment;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkSummary extends Component
{
    public array $counts = [
        'N' => 0,
        'X' => 0,
        'A' => 0,
        'P' => 0,
    ];

    public int $total = 0;

    // ===================================================================
    // Lifecycle & Listeners
    // ===================================================================

    public function mount(): void
    {
        $this->refreshCounts();
    }

    #[On('workAssigned')]
    #[On('confirmRemoveAssignment')]
    #[On('licensesCleared')]
    #[On('tableReset')]
    public function refreshCounts(): void
    {
        $results = WorkAssignment::query()
            ->whereIn('value', ['N', 'X', 'A', 'P'])
            ->selectRaw('value, COUNT(*) as count')
            ->groupBy('value')
            ->pluck('count', 'value');

        $this->counts = [
            'N' => (int) ($results['N'] ?? 0),
            'X' => (int) ($results['X'] ?? 0),
            'A' => (int) ($results['A'] ?? 0),
            'P' => (int) ($results['P'] ?? 0),
        ];

        $this->total = array_sum($this->counts);
    }

    // ===================================================================
    // Render
    // ===================================================================

    public function render()
    {
        return view('livewire.component.work-summary');
    }
}