<?php

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

    // =====================================================================
    // 1. METODI BASE – Restituiscono tutte le collezioni di lavori filtrati
    // =====================================================================

    /** Restituisce tutti i lavori ordinati per timestamp */
    public function allWorks(): Collection
    {
        return collect($this->licenseTable ?? [])
            ->flatMap(fn ($license) => $license['worksMap'] ?? [])
            ->filter()           // rimuove valori null o vuoti
            ->sortBy('timestamp')
            ->values();          // ri-indicizza la Collection
    }

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

    /** Lavori di tipo N o P ancora pendenti */
    public function pendingNPWorks(): Collection
    {
        return $this->sharableWorks()->whereIn('value', ['P', 'N']);
    }

    // =====================================================================
    // 3. PREPARAZIONE MATRICE – Obbligatoria prima di distribuire i lavori
    // =====================================================================
    public function prepareMatrix(): void
    {
        // Riga vuota template
        $emptyRow = [
            'id'                => null,
            'license_table_id'  => null,
            'user'              => null,
            'turn'              => DayType::FULL->value,
            'real_slots_today'  => config('constants.matrix.total_slots'),
            'only_cash_works'   => false,
            'wallet'            => 0,
            'slots_occupied'    => 0,
            'worksMap'          => array_fill(0, config('constants.matrix.total_slots'), null),
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
                'slots_occupied'        => $license['slots_occupied'],
                'wallet'                => $license['wallet'],
                'real_slots_today'      => $license['real_slots_today'] ?? config('constants.matrix.total_slots'),
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
        $time = $this->extractTime($work);
        return $time !== null && $time <= config('constants.matrix.morning_end');
    }

    private function isAfternoon($work): bool
    {
        $time = $this->extractTime($work);
        return $time !== null && $time >= config('constants.matrix.afternoon_start');
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
            'n_or_p_works' => $this->pendingNPWorks()->count(),
            'sample_morning_A' => $this->pendingMorningAgencyWorks()->take(2)->toArray(),
        ];
    }

    /** Somma totale importi dei lavori condivisibili */
    public function totalEarnings(): float
    {
        return (float) $this->sharableWorks()->sum('amount');
    }
}
