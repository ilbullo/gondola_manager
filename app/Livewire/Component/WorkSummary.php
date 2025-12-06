<?php

namespace App\Livewire\Component;

use App\Models\WorkAssignment;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente Livewire che riassume il numero di assegnazioni di lavoro per tipo.
 * Tiene traccia dei conteggi giornalieri per ciascun tipo di lavoro: N, X, A, P.
 */
class WorkSummary extends Component
{
    /**
     * Array dei conteggi dei lavori per tipo.
     * - 'N': tipo N
     * - 'X': tipo X
     * - 'A': tipo A
     * - 'P': tipo P
     *
     * Tutti inizializzati a 0.
     */
    public array $counts = [
        'N' => 0,
        'X' => 0,
        'A' => 0,
        'P' => 0,
    ];

    /**
     * Conteggio totale di tutte le assegnazioni
     */
    public int $total = 0;

    // ===================================================================
    // Lifecycle & Event Listeners
    // ===================================================================

    /**
     * Metodo di mount del componente Livewire.
     * Viene chiamato una volta alla creazione del componente.
     * Qui viene inizializzato il conteggio dei lavori.
     */
    public function mount(): void
    {
        $this->refreshCounts();
    }

    /**
     * Aggiorna i conteggi dei lavori in base ai dati presenti nel database.
     *
     * Ascolta diversi eventi Livewire che possono modificare le assegnazioni:
     * - workAssigned: quando viene assegnato un nuovo lavoro
     * - confirmRemoveAssignment: quando un lavoro viene rimosso
     * - licensesCleared: quando le licenze vengono azzerate
     * - tableReset: quando la tabella delle assegnazioni viene resettata
     *
     * Recupera i conteggi dei tipi di lavoro ['N','X','A','P'] del giorno corrente,
     * e aggiorna le proprietà $counts e $total.
     */
    #[On('workAssigned')]
    #[On('confirmRemoveAssignment')]
    #[On('licensesCleared')]
    #[On('tableReset')]
    public function refreshCounts(): void
    {
        // Esegue una query sul modello WorkAssignment filtrando per tipi specifici e data odierna
        $results = WorkAssignment::query()
            ->whereIn('value', ['N', 'X', 'A', 'P'])
            ->whereDate('timestamp', today())
            ->selectRaw('value, COUNT(*) as count') // conteggia il numero di record per tipo
            ->groupBy('value')
            ->pluck('count', 'value'); // restituisce array associativo ['N'=>count, ...]

        // Aggiorna i conteggi, impostando 0 se un tipo non è presente nella query
        $this->counts = [
            'N' => (int) ($results['N'] ?? 0),
            'X' => (int) ($results['X'] ?? 0),
            'A' => (int) ($results['A'] ?? 0),
            'P' => (int) ($results['P'] ?? 0),
        ];

        // Calcola il totale sommando tutti i conteggi
        $this->total = array_sum($this->counts);
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Renderizza il componente Livewire.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.component.work-summary');
    }
}
