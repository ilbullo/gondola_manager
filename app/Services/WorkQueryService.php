<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DayType;
//use App\Models\WorkAssignment;
use DateTimeInterface;
use Illuminate\Support\Collection;
use App\Contracts\WorkQueryInterface;

/**
 * Class WorkQueryService
 *
 * @package App\Services
 *
 * Specialista nella segmentazione e filtraggio dei carichi di lavoro.
 * Analizza lo stato attuale delle licenze per estrarre code di lavoro prioritarie,
 * applicando regole di business basate su orari, tipologie e flag fiscali.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Aggregation: Estrae e deduplica i lavori dai singoli slot delle licenze.
 * 2. Temporal Logic: Classifica i lavori in fasce orarie (Morning/Afternoon)
 * interfacciandosi con i file di configurazione.
 * 3. Matrix Scaffolding: Crea la struttura dati iniziale (Map) necessaria
 * per le operazioni di scrittura del MatrixEngine.
 * 4. Business Rules Segmentation: Isola i lavori 'Shared from First' per
 * garantire la corretta ripartizione dei costi tra i conducenti.
 */

class WorkQueryService implements WorkQueryInterface
{
    /**
     * Cache locale dei lavori per il ciclo di esecuzione corrente.
     */
    protected ?Collection $cachedWorks = null;

    /**
     * Estrae e deduplica tutti i lavori da una collezione di licenze.
     * Implementa la memoizzazione per evitare ricalcoli costosi.
     */
    public function allWorks(Collection|array $licenseTable): Collection
    {
        // Se i lavori sono già stati calcolati, restituisci la cache
        if ($this->cachedWorks !== null) {
            return $this->cachedWorks;
        }

        // Calcolo originale
        $works = collect($licenseTable)
            ->flatMap(fn ($license) => $license['worksMap'] ?? [])
            ->filter();

        $this->cachedWorks = $works->groupBy('id')
            ->map(fn (Collection $group) => $group->first())
            ->sortBy([
                ['slots_occupied', 'desc'],
                ['timestamp', 'asc'],
            ])
            ->values();

        return $this->cachedWorks;
    }

    /**
     * Importante: Metodo per svuotare la cache se i dati sorgente cambiano.
     */
    public function flushCache(): void
    {
        $this->cachedWorks = null;
    }

    /** Lavori condivisibili e non esclusi */
    public function sharableWorks(Collection|array $licenseTable): Collection
    {
        return $this->allWorks($licenseTable)
            ->where('excluded', false)
            ->where('shared_from_first', false);
    }

    /** Lavori non condivisibili (esclusi) */
    public function unsharableWorks(Collection|array $licenseTable): Collection
    {
        return $this->allWorks($licenseTable)
            ->where('excluded', true)
            ->where('shared_from_first', false);
    }

    /** Lavori condivisibili obbligatori nel primo slot */
    public function sharableFirstWorks(Collection|array $licenseTable): Collection
    {
        return $this->allWorks($licenseTable)
            ->where('excluded', false)
            ->where('shared_from_first', true);
    }

    /** Lavori condivisibili obbligatori nel primo slot di tipo A */
    public function sharableFirstAgencyWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableFirstWorks($licenseTable)
            ->where('value','A');
    }

    /** Lavori condivisibili obbligatori nel primo slot di tipo X */
    public function sharableFirstCashWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableFirstWorks($licenseTable)
            ->where('value','X');
    }

    /** Filtri specifici per tipologia e orario */
    public function pendingMorningAgencyWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableWorks($licenseTable)
            ->filter(fn ($work) => $this->isMorning($work))
            ->where('value', 'A');
    }

    public function pendingAfternoonAgencyWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableWorks($licenseTable)
            ->filter(fn ($work) => $this->isAfternoon($work))
            ->where('value', 'A');
    }

    public function pendingCashWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableWorks($licenseTable)->where('value', 'X');
    }

    public function pendingNWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableWorks($licenseTable)->where('value', 'N');
    }

    public function pendingPWorks(Collection|array $licenseTable): Collection
    {
        return $this->sharableWorks($licenseTable)->where('value', 'P');
    }

    /**
     * Inizializza la matrice vuota partendo dai dati delle licenze.
     */
    public function prepareMatrix(Collection|array $licenseTable): Collection
    {
        $totalSlots = config('app_settings.matrix.total_slots', 25);

        return collect($licenseTable)->map(function ($license) use ($totalSlots) {
            return [
                'id'                => $license['id'] ?? null,
                'user'              => $license['user'] ?? null,
                'turn'              => $license['turn'] ?? DayType::FULL->value,
                'real_slots_today'  => $license['real_slots_today'] ?? $totalSlots,
                'only_cash_works'   => $license['only_cash_works'] ?? false,
                'target_capacity'   => $license['target_capacity'] ?? 0,
                'slots_occupied'    => $license['slots_occupied'] ?? 0,
                'wallet'            => $license['wallet'] ?? 0,
                'worksMap'          => array_fill(1, $totalSlots, null),
            ];
        })->values();
    }

    // =====================================================================
    // Helper Privati
    // =====================================================================

    private function isMorning(array $work): bool
    {
        $time = $this->extractTime($work) ?? '09:01';
        return $time <= config('app_settings.matrix.morning_end');
    }

    private function isAfternoon(array $work): bool
    {
        $time = $this->extractTime($work) ?? '14:00';
        return $time >= config('app_settings.matrix.afternoon_start');
    }

    private function extractTime(array $work): ?string
    {
        $ts = $work['timestamp'] ?? null;
        if (!$ts) return null;

        if ($ts instanceof DateTimeInterface) {
            return $ts->format('H:i');
        }

        if (is_string($ts)) {
            if (strlen($ts) >= 19) return substr($ts, 11, 5);
            if (preg_match('/^(\d{2}:\d{2})/', $ts, $matches)) return $matches[1];
        }

        return null;
    }
}
