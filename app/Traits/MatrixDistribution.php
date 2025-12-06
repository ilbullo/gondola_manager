<?php

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
        return $this->matrix->toArray();
    }

    /**
     * Salva la matrice aggiornata come Collection.
     *
     * @param array $matrix
     */
    private function saveMatrix(array $matrix): void
    {
        $this->matrix = collect($matrix);
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
    private function countFixedWorks(int $key): int
    {
        $original = $this->licenseTable[$key] ?? null;

        if (!$original) {
            throw new RuntimeException('Tabella inesistente per il key specificato.');
        }

        return collect($original['worksMap'])
            ->filter(fn($work) => $work &&
                (in_array($work['value'], ['N', 'P'], true) ||
                 (($work['excluded'] ?? false) === true && $work['value'] !== 'A')))
            ->count();
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
        $matrixItem = $this->matrix->toArray()[$key];
        $totalSlots = $matrixItem['slots_occupied'] ?? 0;

        $usedSlots = collect($matrixItem['worksMap'])
            ->filter()
            ->count();

        if ($forFixed) {
            return $totalSlots - $usedSlots;
        }

        return $totalSlots - $usedSlots - $this->countFixedWorks($key);
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
        $startingIndex = $fromFirst ? array_search(null, $matrix[0]['worksMap'], true) : 0;

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
    public function distributeFixed(Collection $worksToAssign): void
    {
        $matrix = $this->getMatrix();

        foreach ($worksToAssign as $work) {
            $index = array_search($work['license_table_id'], array_column($matrix, 'license_table_id'));
            $slotIndex = array_search(null, $matrix[$index]['worksMap'], true);

            if ($this->getCapacityLeft($index, true) > 0) {
                $matrix[$index]['worksMap'][$slotIndex] = $worksToAssign->shift();
                $this->saveMatrix($matrix);
            } else {
                $this->addToUnassigned($work);
            }
        }
    }
}
