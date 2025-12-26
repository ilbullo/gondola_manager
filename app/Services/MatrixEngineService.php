<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DayType;
use App\Enums\WorkType;
use Illuminate\Support\Collection;
use DateTimeInterface;
use App\Contracts\MatrixEngineInterface;

class MatrixEngineService implements MatrixEngineInterface
{
    private int $totalSlots;

    public function __construct()
    {
        $this->totalSlots = (int) config('app_settings.matrix.total_slots', 25);
    }

    /**
     * Distribuisce lavori fissi alle licenze rispettando la capacità globale.
     * Utilizza $allWorks per calcolare correttamente i 'P' pendenti.
     */
    public function distributeFixed(Collection $worksToAssign, Collection &$matrix, Collection &$unassigned, Collection $allWorks): void
    {
        // Utilizziamo un lookup per ID per l'assegnazione mirata
        $matrixById = $matrix->keyBy('id');

        foreach ($worksToAssign as $work) {
            $licenseId = $work['license_table_id'] ?? null;
            
            if (!$licenseId || !$matrixById->has($licenseId)) {
                $unassigned->push($work);
                continue;
            }

            $license = $matrixById->get($licenseId);
            $slotsNeeded = $work['slots_occupied'] ?? 1;

            // Cerca il blocco di slot liberi
            $startSlot = $this->findConsecutiveFreeSlots($license['worksMap'], $slotsNeeded);

            // Verifica capacità residua passando la collezione globale allWorks
            if ($startSlot === false || $this->getCapacityLeft($license, $allWorks, true) < $slotsNeeded) {
                $unassigned->push($work);
                continue;
            }

            // Assegna il lavoro negli slot trovati
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $targetSlot = $startSlot + $i;
                $license['worksMap'][$targetSlot] = $work;
            }

            // Aggiorna il conteggio degli slot occupati in memoria
            $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();

            $matrixById->put($licenseId, $license);
        }

        // Ripristina la matrice originale con i dati aggiornati
        $matrix->splice(0, $matrix->count(), $matrixById->values()->all());
    }

    /**
     * Distribuisce i lavori in round-robin o verticale.
     * Utilizza $allWorks per calcolare correttamente i 'P' pendenti.
     */
    public function distribute(
        Collection $worksToAssign, 
        Collection &$matrix, 
        Collection &$unassigned, 
        Collection $allWorks, 
        bool $useFirstSlotOnly = false
    ): void {
        if ($worksToAssign->isEmpty()) return;

        // Gestione Logica Verticale (Shared From First)
        if ($useFirstSlotOnly) {
            $this->distributeVertical($worksToAssign, $matrix, $unassigned, $allWorks);
            return;
        }

        // Logica Standard: Colonna per colonna (Slot 1 -> 25)
        $attempts = 0;
        $maxAttempts = $this->totalSlots * $matrix->count();

        while (!$worksToAssign->isEmpty() && $attempts < $maxAttempts) {
            $attempts++;
            $assignedInThisRound = false;

            for ($currentSlot = 1; $currentSlot <= $this->totalSlots; $currentSlot++) {
                if ($worksToAssign->isEmpty()) break 2;

                foreach ($matrix as $index => $license) {
                    if ($worksToAssign->isEmpty()) break 2;

                    $nextWork = $worksToAssign->first();
                    $slotsNeeded = $nextWork['slots_occupied'] ?? 1;

                    // Controllo spazio, turno (allowed) e capacità globale
                    if ($this->canFitAtSlot($license, $currentSlot, $slotsNeeded) &&
                        $this->isAllowedToBeAdded($license, $nextWork) &&
                        $this->getCapacityLeft($license, $allWorks) >= $slotsNeeded) {

                        for ($i = 0; $i < $slotsNeeded; $i++) {
                            $license['worksMap'][$currentSlot + $i] = $nextWork;
                        }
                        
                        $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
                        $matrix[$index] = $license;
                        
                        $worksToAssign->shift();
                        $assignedInThisRound = true;
                        break 2; // Passa al prossimo lavoro
                    }
                }
            }
            if (!$assignedInThisRound) break;
        }

        // Sposta i rimasugli in unassigned
        foreach ($worksToAssign as $work) $unassigned->push($work);
    }

    /**
     * Helper per la distribuzione verticale (Colonna per colonna).
     */
    private function distributeVertical(Collection $worksToAssign, Collection &$matrix, Collection &$unassigned, Collection $allWorks): void
    {
        $firstLicense = $matrix->first();
        if (!$firstLicense) {
            foreach ($worksToAssign as $work) $unassigned->push($work);
            return;
        }

        // Trova il primo slot utile nella prima licenza
        $currentFixedSlot = null;
        for ($slot = 1; $slot <= $this->totalSlots; $slot++) {
            if (!isset($firstLicense['worksMap'][$slot]) || $firstLicense['worksMap'][$slot] === null) {
                $currentFixedSlot = $slot;
                break;
            }
        }

        if ($currentFixedSlot === null) {
            foreach ($worksToAssign as $work) $unassigned->push($work);
            return;
        }

        while (!$worksToAssign->isEmpty() && $currentFixedSlot <= $this->totalSlots) {
            foreach ($matrix as $index => $license) {
                if ($worksToAssign->isEmpty()) break 2;

                $nextWork = $worksToAssign->first();
                $slotsNeeded = $nextWork['slots_occupied'] ?? 1;

                if ($this->canFitAtSlot($license, $currentFixedSlot, $slotsNeeded) &&
                    $this->isAllowedToBeAdded($license, $nextWork) &&
                    $this->getCapacityLeft($license, $allWorks) >= $slotsNeeded) {

                    for ($i = 0; $i < $slotsNeeded; $i++) {
                        $license['worksMap'][$currentFixedSlot + $i] = $nextWork;
                    }
                    
                    $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
                    $matrix[$index] = $license;
                    $worksToAssign->shift();
                }
            }
            $currentFixedSlot++;
        }

        foreach ($worksToAssign as $work) $unassigned->push($work);
    }

    /**
     * RIORDINO RIGHE MATRICE
     */
    public function sortMatrixRows(Collection &$matrix): void
    {
        $matrix = $matrix->map(function ($row) {
            $occupied = array_filter($row['worksMap']);

            usort($occupied, function ($a, $b) {
                $pA = $this->calculatePriority($a);
                $pB = $this->calculatePriority($b);
                return $pA <=> $pB;
            });

            $newMap = [];
            for ($i = 1; $i <= $this->totalSlots; $i++) {
                $newMap[$i] = $occupied[$i - 1] ?? null;
            }
            $row['worksMap'] = $newMap;
            return $row;
        });
    }

    /**
     * HELPER: Cerca blocchi liberi (Stesso nome e logica)
     */
    public function findConsecutiveFreeSlots(array $worksMap, int $slotsNeeded): int|false
    {
        for ($startSlot = 1; $startSlot <= $this->totalSlots - $slotsNeeded + 1; $startSlot++) {
            $isFreeBlock = true;
            for ($i = 0; $i < $slotsNeeded; $i++) {
                if (($worksMap[$startSlot + $i] ?? null) !== null) {
                    $isFreeBlock = false;
                    break;
                }
            }
            if ($isFreeBlock) return $startSlot;
        }
        return false;
    }

    /**
     * Calcola la capacità residua per una licenza.
     * Rispetta il target_capacity sottraendo i lavori di tipo P associati alla licenza.
     *
     * @param array $license      La riga della licenza corrente (array dalla matrice).
     * @param Collection $allWorks La collezione globale di tutti i lavori della giornata (per contare i P).
     * @param bool $useTargetLimit Se TRUE, il limite massimo è target_capacity. Altrimenti è il limite fisico (25).
     * @return int La capacità residua (slot liberi).
     */
    public function getCapacityLeft(array $license, Collection $allWorks, bool $useTargetLimit = true): int
    {
        // 1. Determina la capacità MASSIMA (il denominatore corretto)
        // Contiamo i lavori 'P' associati a questa licenza nella lista globale dei lavori.
        // Questi lavori riducono il target_capacity disponibile per altri tipi di lavoro.
        $numberOfPWorks = $allWorks
            ->where('value', 'P')
            ->where('license_table_id', $license['id'])
            ->count();

        $targetCapacity = ($license['target_capacity'] ?? 0) - $numberOfPWorks;

        // 2. Determina il limite da utilizzare
        if ($useTargetLimit) {
            $limit = (int) $targetCapacity;
        } else {
            // Se non usiamo il limite target, usiamo il limite fisico degli slot (es. 25)
            $limit = $this->totalSlots;
        }

        // 3. Determina gli slot attualmente occupati (il numeratore)
        // Utilizziamo il conteggio effettivo nella worksMap attuale (lavori già distribuiti)
        $occupiedSlots = collect($license['worksMap'])->filter()->count();

        // 4. Calcola la capacità residua (Limite - Occupati)
        $capacityLeft = $limit - $occupiedSlots;

        // Ritorna almeno 0 (evita valori negativi se la licenza è sovraccarica)
        return (int) max(0, $capacityLeft);
    }
    /**
     * HELPER: Permesso inserimento (Stesso nome e logica)
     */
    private function isAllowedToBeAdded(array $license, array $work): bool
    {
        $turn = $license['turn'] ?? DayType::FULL->value;
        $onlyCash = $license['only_cash_works'] ?? false;

        if ($turn !== DayType::FULL->value) {
            $workTime = $this->extractWorkTime($work);
            if ($turn === DayType::MORNING->value && $workTime > config('app_settings.matrix.morning_end')) return false;
            if ($turn === DayType::AFTERNOON->value && $workTime < config('app_settings.matrix.afternoon_start')) return false;
        }

        if ($onlyCash && ($work['value'] ?? '') === WorkType::AGENCY->value) return false;

        return true;
    }

    /**
     * HELPER: Calcolo priorità per sortMatrixRows
     */
    private function calculatePriority(array $work): int
    {
        $v = $work['value'] ?? '';
        $e = $work['excluded'] ?? false;
        $s = $work['shared_from_first'] ?? false;
        $ts = $work['timestamp'] ?? null;

        return match (true) {
            $v === 'A' && $e && !$s => 100,
            $v === 'A' && !$e && !$s => 200,
            $v === 'A' && $s => 300,
            $v === 'X' && $e => 400,
            $v === 'X' && !$e => 500,
            $v === 'N' => 600,
            $v === 'P' => 700,
            default => 1000 + ($ts ? (is_string($ts) ? strtotime($ts) : $ts->getTimestamp()) : 999999),
        };
    }

    /**
     * HELPER: Controllo sovrapposizione slot
     */
    private function canFitAtSlot(array $license, int $startSlot, int $slotsNeeded): bool
    {
        for ($i = 0; $i < $slotsNeeded; $i++) {
            $checkSlot = $startSlot + $i;
            if ($checkSlot > $this->totalSlots || ($license['worksMap'][$checkSlot] ?? null) !== null) {
                return false;
            }
        }
        return true;
    }

    private function extractWorkTime(array $work): string
    {
        $ts = $work['timestamp'] ?? null;
        if ($ts instanceof DateTimeInterface) return $ts->format('H:i');
        return (is_string($ts) && strlen($ts) >= 19) ? substr($ts, 11, 5) : '00:00';
    }
}