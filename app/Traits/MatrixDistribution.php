<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\DayType;
use App\Enums\WorkType;
use Illuminate\Support\Collection;
use RuntimeException;
use DateTimeInterface;

trait MatrixDistribution
{

    // =====================================================================
    // 1. GET / SAVE MATRICE
    // =====================================================================

    /**
     * Restituisce la matrice come array.
     *
     * @return array
     */
    private function getMatrix(): array
    {
        return $this->matrix->all();
    }

    /**
     * Salva la matrice aggiornata come Collection.
     *
     * @param array $matrix
     */
    private function saveMatrix(array $matrix): void
    {
        $this->matrix = collect($matrix)->values();
    }

    // =====================================================================
    // 2. HELPER PRIVATI – Controlli e calcoli
    // =====================================================================

    /**
     * Cerca N slot consecutivi liberi in worksMap.
     *
     * @param array $worksMap La worksMap della licenza (1-based index).
     * @param int $slotsNeeded Il numero di slot consecutivi richiesti.
     * @return int|false L'indice di partenza (1-based) del blocco libero, o false.
     */
    private function findConsecutiveFreeSlots(array $worksMap, int $slotsNeeded): int|false
    {
        $totalSlots = count($worksMap);

        for ($startSlot = 1; $startSlot <= $totalSlots - $slotsNeeded + 1; $startSlot++) {
            $isFreeBlock = true;

            // Controlla l'intero blocco di slot richiesto
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $checkSlot = $startSlot + $i;

                // Se lo slot è occupato (non null)
                if (($worksMap[$checkSlot] ?? null) !== null) {
                    $isFreeBlock = false;
                    break;
                }
            }

            if ($isFreeBlock) {
                return $startSlot;
            }
        }

        return false;
    }

    /**
     * Conta i lavori fissi (N, P o esclusi non A) per una licenza.
     *
     * @param int $key
     * @return int
     * @throws RuntimeException
     */
   /* private function countFixedWorks(int $key): int
    {
        $original = $this->licenseTable[$key] ?? null;

        if (!$original) {
            throw new RuntimeException('Tabella inesistente per il key specificato.');
        }

        $collection = collect($original['worksMap']);

        return $collection
            ->filter(fn($work) => $work &&
                (in_array($work['value'], ['N', 'P'], true) ||
                 (($work['excluded'] ?? false) === true && $work['value'] !== 'A')))
            ->count();
    }*/

    /**
     * Riordina ogni riga della matrice con l'ordine visivo richiesto:
     * 1. A fissi (excluded == true)
     * 2. A normali (excluded == false)
     * 3. A shared_from_first (shared_from_first == true)
     * 4. X fissi (excluded == true)
     * 5. X normali (excluded == false)
     * 6. N
     * 7. P
     * 8. Tutti gli altri (ordinati per orario)
     */
    private function sortMatrixRows(): void
    {
        $matrix = $this->getMatrix();
        $totalSlots = config('app_settings.matrix.total_slots', 25);

        foreach ($matrix as $key => &$row) { // Reference
            $worksMap = $row['worksMap'];
            $occupied = array_filter($worksMap); // Array nativo per velocità

            usort($occupied, function ($a, $b) {
                $aNorm = is_array($a) ? $a : ['value' => $a, 'excluded' => false, 'shared_from_first' => false];
                $bNorm = is_array($b) ? $b : ['value' => $b, 'excluded' => false, 'shared_from_first' => false];

                $aValue = $aNorm['value'] ?? null;
                $aExcluded = $aNorm['excluded'] ?? false;
                $aShared = $aNorm['shared_from_first'] ?? false;
                $aTs = $aNorm['timestamp'] ?? null;

                $bValue = $bNorm['value'] ?? null;
                $bExcluded = $bNorm['excluded'] ?? false;
                $bShared = $bNorm['shared_from_first'] ?? false;
                $bTs = $bNorm['timestamp'] ?? null;

                $aPriority = match (true) {
                    $aValue === 'A' && $aExcluded && !$aShared => 100,
                    $aValue === 'A' && !$aExcluded && !$aShared => 200,
                    $aValue === 'A' && $aShared => 300,
                    $aValue === 'X' && $aExcluded => 400,
                    $aValue === 'X' && !$aExcluded => 500,
                    $aValue === 'N' => 600,
                    $aValue === 'P' => 700,
                    default => 1000 + ($aTs ? strtotime($aTs) : PHP_INT_MAX),
                };

                $bPriority = match (true) {
                    $bValue === 'A' && $bExcluded && !$bShared => 100,
                    $bValue === 'A' && !$bExcluded && !$bShared => 200,
                    $bValue === 'A' && $bShared => 300,
                    $bValue === 'X' && $bExcluded => 400,
                    $bValue === 'X' && !$bExcluded => 500,
                    $bValue === 'N' => 600,
                    $bValue === 'P' => 700,
                    default => 1000 + ($bTs ? strtotime($bTs) : PHP_INT_MAX),
                };

                return $aPriority <=> $bPriority;
            });

            $row['worksMap'] = array_pad($occupied, $totalSlots, null);
        }

        $this->saveMatrix($matrix);
    }

    /**
     * Controlla se un lavoro può essere aggiunto alla licenza.
     *
     * @param int $key
     * @param array $work
     * @return bool
     */
    private function isAllowedToBeAdded(int $key, array $work): bool
    {
        $matrixItem = $this->matrix->toArray()[$key];
        $turn = $matrixItem['turn'];
        $onlyCash = $matrixItem['only_cash_works'];
        $value = $work['value'];

        // Rispetto turno mezza giornata
        if (in_array($turn, [DayType::MORNING->value, DayType::AFTERNOON->value], true)) {
            $workTime = $this->extractWorkTime($work);

            if ($turn === DayType::MORNING->value && $workTime > config('app_settings.matrix.morning_end')) {
                return false;
            }
            if ($turn === DayType::AFTERNOON->value && $workTime < config('app_settings.matrix.afternoon_start')) {
                return false;
            }
        }

        // Solo lavori cash se licenza cash-only
        if ($onlyCash && $value === WorkType::AGENCY->value) {
            return false;
        }

        return true;
    }

    /**
     * Calcola la capacità residua esattamente come nel metodo originale.
     * * @param array $license La riga della matrice corrente
     * @param Collection $allWorks La collezione di tutti i lavori (per trovare i P pendenti)
     * @param bool $useTargetLimit
     * @return int
     */
    public function getCapacityLeft(array $license, Collection $allWorks, bool $useTargetLimit = true): int
    {
        // 1. Determina la capacità MASSIMA (il denominatore corretto)
        // Contiamo i lavori 'P' associati a questa licenza dalla lista totale dei lavori
        $numberOfPWorks = $allWorks
            ->where('value', 'P')
            ->where('license_table_id', $license['id'])
            ->count();

        $targetCapacity = ($license['target_capacity'] ?? 0) - $numberOfPWorks;

        // 2. Se stiamo usando il limite target, usiamo quello, altrimenti il limite fisico (25)
        $limit = $useTargetLimit 
            ? $targetCapacity 
            : (int) config('app_settings.matrix.total_slots', 25);

        // 3. Determina gli slot attualmente occupati (il numeratore)
        // Conteggio effettivo dei lavori già posizionati nella worksMap
        $occupiedSlots = collect($license['worksMap'])->filter()->count();

        // 4. Calcola la capacità residua (Limite - Occupati)
        $capacityLeft = $limit - $occupiedSlots;

        return (int) max(0, $capacityLeft);
    }

    /**
     * Aggiunge un lavoro alla lista dei lavori non assegnati.
     *
     * @param array $work
     */
    private function addToUnassigned(array $work): void
    {
        $this->unassignedWorks->push($work);
    }

    /**
     * Estrae l'orario HH:ii dal timestamp del lavoro.
     *
     * @param array $work
     * @return string
     */
    private function extractWorkTime(array $work): string
    {
        $ts = $work['timestamp'] ?? null;

        if ($ts instanceof DateTimeInterface) {
            return $ts->format('H:i');
        }

        if (is_string($ts) && strlen($ts) >= 19) {
            return substr($ts, 11, 5);
        }

        return '00:00';
    }

    // =====================================================================
    // 3. DISTRIBUZIONE LAVORI
    // =====================================================================

/**
     * DISTRIBUZIONE LAVORI FISSI
     * Adattato per passare $allWorks a getCapacityLeft
     */
    public function distributeFixed(Collection $worksToAssign, Collection &$matrix, Collection &$unassigned): void
    {
        // Lookup per ID per l'assegnazione diretta alla licenza corretta
        $matrixById = $matrix->keyBy('id');

        foreach ($worksToAssign as $work) {
            $licenseId = $work['license_table_id'] ?? null;
            
            if (!$licenseId || !$matrixById->has($licenseId)) {
                $unassigned->push($work);
                continue;
            }

            $license = $matrixById->get($licenseId);
            $slotsNeeded = $work['slots_occupied'] ?? 1;

            $startSlot = $this->findConsecutiveFreeSlots($license['worksMap'], $slotsNeeded);

            // CHIAMATA AGGIORNATA: Passiamo $license e la collezione completa dei lavori
            if ($startSlot === false || $this->getCapacityLeft($license, $worksToAssign, true) < $slotsNeeded) {
                $unassigned->push($work);
                continue;
            }

            // Assegnazione
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $license['worksMap'][$startSlot + $i] = $work;
            }

            $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
            $matrixById->put($licenseId, $license);
        }

        // Sincronizza la collection originale passata per riferimento
        $matrix->splice(0, $matrix->count(), $matrixById->values()->all());
    }

    /**
     * DISTRIBUZIONE ROUND-ROBIN / VERTICALE
     * Adattato per passare $allWorks a getCapacityLeft
     */
    public function distribute(
        Collection $worksToAssign, 
        Collection &$matrix, 
        Collection &$unassigned, 
        bool $useFirstSlotOnly = false
    ): void {
        if ($worksToAssign->isEmpty()) return;

        if ($useFirstSlotOnly) {
            $this->distributeVertical($worksToAssign, $matrix, $unassigned);
            return;
        }

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

                    // CHIAMATA AGGIORNATA: Passiamo $license e la collezione completa dei lavori
                    if ($this->canFitAtSlot($license, $currentSlot, $slotsNeeded) &&
                        $this->isAllowedToBeAdded($license, $nextWork) &&
                        $this->getCapacityLeft($license, $worksToAssign) >= $slotsNeeded) {

                        for ($i = 0; $i < $slotsNeeded; $i++) {
                            $license['worksMap'][$currentSlot + $i] = $nextWork;
                        }
                        
                        $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
                        $matrix[$index] = $license;
                        $worksToAssign->shift();
                        $assignedInThisRound = true;
                        break 2;
                    }
                }
            }
            if (!$assignedInThisRound) break;
        }

        foreach ($worksToAssign as $work) $unassigned->push($work);
    }

}
