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
        $totalSlots = config('constants.matrix.total_slots', 25);

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

            if ($turn === DayType::MORNING->value && $workTime > config('constants.matrix.morning_end')) {
                return false;
            }
            if ($turn === DayType::AFTERNOON->value && $workTime < config('constants.matrix.afternoon_start')) {
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
     * Restituisce la capacità residua di una licenza.
     *
     * @param int $key
     * @param bool $forFixed
     * @return int
     */
    private function getCapacityLeft(int $key, bool $forFixed = false): int
    {
        $matrixItem = $this->matrix->get($key);
        if (!$matrixItem) return 0;

        $totalSlots = $matrixItem['slots_occupied'] ?? 0;
        
        // Conta solo gli slot fisicamente occupati (indipendentemente dal tipo)
        $occupied = collect($matrixItem['worksMap'])
            ->filter(fn($work) => !is_null($work))
            ->count();

        return $totalSlots - $occupied;
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
     * Distribuisce i lavori in round-robin rispettando:
     * - turno mezza giornata (morning/afternoon)
     * - capacità residua
     * - primo slot libero disponibile
     * - round-robin
     *
     * @param Collection $worksToAssign
     * @param bool $fromFirst Se true parte dallo slot iniziale
     */
    public function distribute(Collection $worksToAssign, bool $fromFirst = false): void
    {
        $maxSlotsIndex = config('constants.matrix.total_slots') - 1;
        $matrix = $this->getMatrix();
        $startingIndex = $fromFirst
            ? collect($this->matrix->first()['worksMap'] ?? [])->search(null, true)
            : 0;
        for ($slotIndex = $startingIndex; $slotIndex <= $maxSlotsIndex; $slotIndex++) {
            foreach ($this->matrix as $key => $licenseData) {
                $work = $licenseData['worksMap'][$slotIndex] ?? null;
                if (!is_null($work)) continue;

                if ($this->getCapacityLeft($key) > 0 && !$worksToAssign->isEmpty()) {
                    $nextWork = $worksToAssign->first();
                    if ($this->isAllowedToBeAdded($key, $nextWork)) {
                        $matrix[$key]['worksMap'][$slotIndex] = $nextWork;
                        $this->saveMatrix($matrix);
                        $worksToAssign->shift();
                    }
                    if ($worksToAssign->isEmpty()) break 2;
                }
            }
        }

        foreach ($worksToAssign as $work) {
            $this->addToUnassigned($work);
        }
    }

    /**
     * Distribuisce lavori fissi alle licenze rispettando la capacità.
     *
     * @param Collection $worksToAssign
     */
    /**
 * Distribuisce lavori fissi alle licenze rispettando la capacità.
 */
    public function distributeFixed(Collection $worksToAssign): void
    {
        $this->matrix = $this->matrix->keyBy('license_table_id');

        foreach ($worksToAssign as $work) {
            $licenseId = $work['license_table_id'] ?? null;
            if (!$licenseId || !$this->matrix->has($licenseId)) {
                $this->addToUnassigned($work);
                continue;
            }

            $license = $this->matrix[$licenseId];
            $freeSlot = collect($license['worksMap'])->search(null, true);

            if ($freeSlot === false || $this->getCapacityLeft($licenseId, true) <= 0) {
                $this->addToUnassigned($work);
                continue;
            }

            $license['worksMap'][$freeSlot] = $work;
            $this->matrix[$licenseId] = $license;
        }

        // Ripristina indici numerici (importante per round-robin)
        $this->matrix = $this->matrix->values();
    }
}
