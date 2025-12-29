<?php

namespace App\Livewire\Component;

use App\Models\WorkAssignment;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Class WorkSummary
 *
 * @package App\Livewire\Component
 *
 * Gestisce la logica di aggregazione e visualizzazione delle statistiche giornaliere.
 * Questo componente funge da monitor globale per i carichi di lavoro, fornendo un riepilogo
 * quantitativo suddiviso per tipologia (N, X, A, P).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Real-time Aggregation: Calcola dinamicamente i totali dal database senza necessità
 * di ricaricare la pagina, grazie all'integrazione con gli eventi di sistema.
 * 2. Multi-Event Reactivity: Centralizza la logica di aggiornamento ascoltando molteplici
 * trigger (creazione, rimozione, reset), garantendo l'integrità del dato visualizzato.
 * 3. Data Integrity: Assicura che i conteggi siano sempre riferiti alla data odierna,
 * agendo come filtro temporale per le metriche di business.
 * 4. UX Feedback: Fornisce all'amministratore una visione d'insieme immediata sul totale
 * delle operazioni effettuate, facilitando il controllo a fine turno.
 *
 * LOGICA DI QUERY:
 * Utilizza raggruppamenti SQL (GroupBy) per massimizzare le performance, evitando di
 * caricare i modelli completi e limitando il carico sulla memoria.
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
