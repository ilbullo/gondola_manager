<?php

// app/Traits/HasWorkQueries.php

namespace App\Traits;

use App\Enums\DayType;
use DateTimeInterface;
use Illuminate\Support\Collection;

trait HasWorkQueries
{
    //use DistributesWorksToMatrix;

    /** @var array|Collection */
    public $licenseTable = [];

    /** @var Collection */
    public $matrix;

    // =====================================================================
    // 1. METODI BASE – Tutti funzionano e restituiscono Collection
    // =====================================================================

    public function allWorks(): Collection
    {
        return collect($this->licenseTable ?? [])
            ->flatMap(fn ($license) => $license['worksMap'] ?? [])
            ->filter()
            ->sortBy('timestamp')
            ->values(); // rimuove null e valori vuoti
    }

    public function sharableWorks(): Collection
    {
        return $this->allWorks()
            ->where('excluded', false)
            ->where('shared_from_first', false);
    }

    public function unsharableWorks() : Collection
    {
        return $this->allWorks()
            ->where('excluded',true)
            ->where('shared_from_first',false);
    }

    public function sharableFirstWorks() : Collection
    {
        return $this->allWorks()
            ->where('excluded',false)
            ->where('shared_from_first',true);
    }

    public function morningWorks(): Collection
    {
        return $this->sharableWorks()->filter(fn ($work) => $this->isMorning($work));
    }

    public function afternoonWorks(): Collection
    {
        return $this->sharableWorks()->filter(fn ($work) => $this->isAfternoon($work));
    }

    // =====================================================================
    // 2. METODI PRONTI ALL'USO – USA QUESTI NEL SERVICE (FUNZIONANO!)
    // =====================================================================

    public function fixedAgencyWorks() : Collection
    {
        return $this->unsharableWorks()->where('value','A');
    }

    public function pendingMorningAgencyWorks(): Collection
    {
        return $this->morningWorks()->where('value', 'A');
    }

    public function pendingAfternoonAgencyWorks(): Collection
    {
        return $this->afternoonWorks()->where('value', 'A');
    }

    public function pendingCashWorks(): Collection
    {
        return $this->sharableWorks()->where('value', 'X');
    }

    public function pendingNPWorks(): Collection
    {
        return $this->sharableWorks()->whereIn('value', ['P', 'N']);
    }


    // =====================================================================
    // 3. PREPARE MATRIX – OBBLIGATORIO PRIMA DI DISTRIBUTE
    // =====================================================================

    public function prepareMatrix(): void
    {
        $emptyRow = [
            'id'                => null,
            'license_table_id'  => null,
            'user'              => null,
            'turn'             => DayType::FULL->value,
            'real_slots_today'  => 25,
            'only_cash_works'   => false,
            'wallet'            => 0,
            'slots_occupied'    => 0,
            'worksMap'          => array_fill(0, 25, null),
        ];

        $this->matrix = collect($this->licenseTable ?? [])
            ->map(fn () => $emptyRow)
            ->values();

        foreach ($this->licenseTable ?? [] as $index => $license) {
            $this->matrix[$index] = array_merge($this->matrix[$index], [
                'id'                    => $license['id'] ?? null,
                'license_table_id'      => $license['id'] ?? null,        // ← IMPORTANTE
                'user'                  => $license['user'] ?? null,
                'turn'                  => $license['turn'] ?? DayType::FULL->value,
                'only_cash_works'       => $license['only_cash_works'],
                'slots_occupied'        => $license['slots_occupied'],
                'wallet'                => $license['wallet'],
                'real_slots_today'      => $license['real_slots_today'] ?? 25,
            ]);
        }

        $this->matrix = $this->matrix->values(); // indici 0,1,2... per round-robin
    }

    // =====================================================================
    // 4. HELPER PRIVATI – Robustissimi
    // =====================================================================

    private function isMorning($work): bool
    {
        $time = $this->extractTime($work);

        return $time !== null && $time <= '13:00';
    }

    private function isAfternoon($work): bool
    {
        $time = $this->extractTime($work);

        return $time !== null && $time >= '13:31';
    }

    private function extractTime($work): ?string
    {
        $ts = $work['timestamp'] ?? null;
        if (! $ts) {
            return null;
        }

        // DateTime object
        if ($ts instanceof DateTimeInterface) {
            return $ts->format('H:i');
        }

        // Stringa completa: "2025-11-28 12:30:00"
        if (is_string($ts) && strlen($ts) >= 19) {
            return substr($ts, 11, 5);
        }

        // Solo orario: "14:30:00" o "14:30"
        if (is_string($ts) && preg_match('/^(\d{2}:\d{2})/', $ts, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // =====================================================================
    // 5. DEBUG & REPORT (opzionali ma utili)
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

    public function totalEarnings(): float
    {
        return (float) $this->sharableWorks()->sum('amount');
    }
}
