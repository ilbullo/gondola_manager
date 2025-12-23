<?php
declare(strict_types=1);

// app/Traits/HasWorkQueries.php

namespace App\Traits;

use App\Enums\DayType;
use DateTimeInterface;
use Illuminate\Support\Collection;

trait HasWorkQueries
{
    // =====================================================================
    // Proprietà del trait
    // =====================================================================
    /** @var array|Collection */
    public $licenseTable = [];   // Array o Collection di licenze da cui derivare i lavori

    /** @var Collection */
    public $matrix;              // Matrice preparata per l'assegnazione dei lavori

    /** @var Collection */
    private ?Collection $cachedAllWorks = null; // Cache per performance

    // =====================================================================
    // 1. METODI BASE – Restituiscono tutte le collezioni di lavori filtrati
    // =====================================================================

    /** Restituisce tutti i lavori ordinati per timestamp */
   /*VERSIONE PRECEDENTE FUNZIONANTE **/
   public function allWorks(): Collection
    {
        if ($this->cachedAllWorks !== null) {
            return $this->cachedAllWorks;
        }

        // 1. Raccogli tutti i lavori da worksMap di ogni licenza (questi sono i duplicati per slot)
        $works = collect($this->licenseTable)
            ->flatMap(fn ($license) => $license['worksMap'] ?? [])
            ->filter();

        // 2. Raggruppa per ID del lavoro e prendi solo il primo elemento (deduplica)
        $this->cachedAllWorks = $works
            ->groupBy('id') // Raggruppa i duplicati con lo stesso ID
            ->map(fn (Collection $group) => $group->first()) // Prendi solo il primo lavoro del gruppo (contiene slots_occupied corretto)
            //->sortByDesc('slots_occupied') // Ordina come richiesto
            //->sortBy('timestamp')
            ->sortBy([
                // Primo criterio: slots_occupied decrescente (più slot in alto)
                ['slots_occupied', 'desc'],
                // Secondo criterio: timestamp crescente (più presto in alto)
                ['timestamp', 'asc'],
            ])
            ->values(); // Reset degli indici
        
        return $this->cachedAllWorks;
    }
/*
   public function allWorks(): Collection
    {
        if ($this->cachedAllWorks !== null) {
            return $this->cachedAllWorks;
        }

        $this->cachedAllWorks = collect($this->licenseTable)
            ->flatMap(fn ($license) => $license['worksMap'] ?? [])
            ->filter()
            // Deduplicazione: prendi il lavoro completo (quello non continuation)
            ->filter(fn ($work) => !($work['is_continuation'] ?? false))
            // Ordinamento: prima slots_occupied DESC, poi timestamp ASC
            ->sort(function ($a, $b) {
                $slotsA = $a['slots_occupied'] ?? 1;
                $slotsB = $b['slots_occupied'] ?? 1;

                if ($slotsA !== $slotsB) {
                    return $slotsB <=> $slotsA; // DESC su slots_occupied
                }

                $timeA = $a['timestamp'] instanceof \Carbon\Carbon
                    ? $a['timestamp']->timestamp
                    : strtotime($a['timestamp'] ?? 'now');

                $timeB = $b['timestamp'] instanceof \Carbon\Carbon
                    ? $b['timestamp']->timestamp
                    : strtotime($b['timestamp'] ?? 'now');

                return $timeA <=> $timeB; // ASC su timestamp
            })
            ->values();

        return $this->cachedAllWorks;
    }*/

    /** Lavori condivisibili e non esclusi */
    public function sharableWorks(): Collection
    {
        return $this->allWorks()
            ->where('excluded', false)
            ->where('shared_from_first', false);
    }

    /** Lavori non condivisibili (excluded = true) */
    public function unsharableWorks() : Collection
    {
        return $this->allWorks()
            ->where('excluded', true)
            ->where('shared_from_first', false);
    }

    /** Lavori condivisibili ma obbligatoriamente nel primo slot */
    public function sharableFirstWorks() : Collection
    {
        return $this->allWorks()
            ->where('excluded', false)
            ->where('shared_from_first', true);
    }

    /** Lavori condivisibili ma obbligatoriamente nel primo slot tipo A -> Agency */
    public function sharableFirstAgencyWorks() : Collection
    {
        return $this->sharableFirstWorks()
                    ->where('value', 'A');
    }

    /** Lavori condivisibili ma obbligatoriamente nel primo slot tipo X -> Cash */
    public function sharableFirstCashWorks() : Collection
    {
        return $this->sharableFirstWorks()
                    ->where('value', 'X');
    }

    /** Lavori della mattina (sharable) */
    public function morningWorks(): Collection
    {
        return $this->sharableWorks()->filter(fn ($work) => $this->isMorning($work));
    }

    /** Lavori del pomeriggio (sharable) */
    public function afternoonWorks(): Collection
    {
        return $this->sharableWorks()->filter(fn ($work) => $this->isAfternoon($work));
    }

    // =====================================================================
    // 2. METODI PRONTI ALL'USO – Da usare direttamente nel service
    // =====================================================================

    /** Lavori di agenzia non condivisibili (fissi) */
    public function fixedAgencyWorks() : Collection
    {
        return $this->unsharableWorks()->where('value','A');
    }

    /** Lavori in contanti non condicisibili (fissi) */
    public function fixedCashWorks(): Collection
    {
        return $this->unsharableWorks()->where('value', 'X');
    }

    /** Lavori mattutini di agenzia ancora pendenti */
    public function pendingMorningAgencyWorks(): Collection
    {
        return $this->morningWorks()->where('value', 'A');
    }

    /** Lavori pomeridiani di agenzia ancora pendenti */
    public function pendingAfternoonAgencyWorks(): Collection
    {
        return $this->afternoonWorks()->where('value', 'A');
    }

    /** Lavori in contanti ancora pendenti */
    public function pendingCashWorks(): Collection
    {
        return $this->sharableWorks()->where('value', 'X');
    }

    /** Lavori di tipo N ancora pendenti */
    public function pendingNWorks(): Collection
    {
        return $this->sharableWorks()->where('value', 'N');
    }

    /* Lavori di tipo P ancora pendenti */
    public function pendingPWorks(): Collection
    {
        return $this->sharableWorks()->where('value', 'P');
    }

    // =====================================================================
    // 3. PREPARAZIONE MATRICE – Obbligatoria prima di distribuire i lavori
    // =====================================================================
    public function prepareMatrix(): void
    {
        $totalSlots = config('app_settings.matrix.total_slots', 25);
        // Riga vuota template
        $emptyRow = [
            'id'                => null,
            'license_table_id'  => null,
            'user'              => null,
            'turn'              => DayType::FULL->value,
            'real_slots_today'  => $totalSlots,
            'only_cash_works'   => false,
            'wallet'            => 0,
            'slots_occupied'    => 0,
            'worksMap'          => array_fill(0, $totalSlots, null),
        ];

        // Crea la matrice base con tante righe quante licenze
        $this->matrix = collect($this->licenseTable ?? [])
            ->map(fn () => $emptyRow)
            ->values();

        // Merge dei dati reali delle licenze nella matrice
        foreach ($this->licenseTable ?? [] as $index => $license) {
            $this->matrix[$index] = array_merge($this->matrix[$index], [
                'id'                    => $license['id'] ?? null,
                'license_table_id'      => $license['id'] ?? null,
                'user'                  => $license['user'] ?? null,
                'turn'                  => $license['turn'] ?? DayType::FULL->value,
                'only_cash_works'       => $license['only_cash_works'],
                'target_capacity'       => $license['target_capacity'],
                'slots_occupied'        => $license['slots_occupied'],
                'wallet'                => $license['wallet'],
                'real_slots_today'      => $license['real_slots_today'] ?? $totalSlots,
            ]);
        }

        // Assicura che gli indici siano 0,1,2... utili per round-robin
        $this->matrix = $this->matrix->values();
    }

    // =====================================================================
    // 4. HELPER PRIVATI – Determinano turno della giornata
    // =====================================================================
    private function isMorning($work): bool
    {
        $time = $this->extractTime($work) ?? '09:01';
        return $time !== null && $time <= config('app_settings.matrix.morning_end');
    }

    private function isAfternoon($work): bool
    {
        $time = $this->extractTime($work) ?? '14:00';
        return $time !== null && $time >= config('app_settings.matrix.afternoon_start');
    }

    /** Estrae l'orario da timestamp in diversi formati */
    private function extractTime($work): ?string
    {
        $ts = $work['timestamp'] ?? null;
        if (! $ts) return null;

        if ($ts instanceof DateTimeInterface) {
            return $ts->format('H:i');
        }

        if (is_string($ts) && strlen($ts) >= 19) {
            return substr($ts, 11, 5); // "HH:MM" da "YYYY-MM-DD HH:MM:SS"
        }

        if (is_string($ts) && preg_match('/^(\d{2}:\d{2})/', $ts, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // =====================================================================
    // 5. DEBUG & REPORT – Facoltativi ma utili durante sviluppo
    // =====================================================================
    public function debugInfo(): array
    {
        return [
            'total_licenses' => count($this->licenseTable ?? []),
            'total_works' => $this->allWorks()->count(),
            'sharable_works' => $this->sharableWorks()->count(),
            'morning_agencies' => $this->pendingMorningAgencyWorks()->count(),
            'afternoon_agencies' => $this->pendingAfternoonAgencyWorks()->count(),
            'extra_works' => $this->pendingCashWorks()->count(),
            'n_or_p_works' => $this->pendingNWorks()->count() + $this->pendingPWorks()->count(),
            'sample_morning_A' => $this->pendingMorningAgencyWorks()->take(2)->toArray(),
        ];
    }

    /** Somma totale importi dei lavori condivisibili */
    public function totalEarnings(): float
    {
        return (float) $this->sharableWorks()->sum('amount');
    }
}
