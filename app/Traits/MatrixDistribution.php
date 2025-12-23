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
     * Calcola la capacità residua per una licenza.
     * Ora limitata da 'target_capacity' anziché dal totale degli slot fisici (25).
     *
     * @param int|string $key La chiave (indice array o license_table_id) della licenza nella matrice.
     * @param bool $isFixed Se true, usa la chiave come license_table_id (usato in distributeFixed).
     * @param bool $useTargetLimit Se TRUE, il limite massimo è target_capacity. Altrimenti è 25.
     * @return int La capacità residua (slot liberi).
     */

    public function getCapacityLeft(int|string $key, bool $isFixed = false, bool $useTargetLimit = true): int
    {
        // 1. Recupera l'oggetto licenza
        if ($isFixed) {
            $license = $this->matrix[$key] ?? null;
        } else {
            $license = $this->matrix->get($key);
        }

        if (!$license) {
            return 0;
        }
        
        // 2. Determina la capacità MASSIMA (il denominatore corretto)
        $numberOfPWorks = $this->pendingPWorks()->where('license_table_id',$license['license_table_id'])->count();
        $targetCapacity = $license['target_capacity'] - $numberOfPWorks ?? 0;

        // Se stiamo usando il limite target, usiamo quello.
        if ($useTargetLimit) {
            $limit = $targetCapacity;
        } else {
            // Se non usiamo il limite target, usiamo il limite fisico (25)
            $limit = config('app_settings.matrix.total_slots');
        }

        // 3. Determina gli slot attualmente occupati (il numeratore)
        // Utilizziamo il conteggio effettivo nella worksMap, che include i lavori distribuiti
        $occupiedSlots = collect($license['worksMap'])->filter()->count();

        // 4. Calcola la capacità residua (Limite - Occupati)
        $capacityLeft = $limit - $occupiedSlots;

        return max(0, $capacityLeft);
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
    public function distribute(Collection $worksToAssign, bool $useFirstSlotOnly = false): void
    {
        if ($worksToAssign->isEmpty()) {
            return;
        }

        $matrix = $this->getMatrix();
        $totalSlots = config('app_settings.matrix.total_slots', 25);

        if ($useFirstSlotOnly) {
            // ===================================================================
            // LOGICA VERTICALE PER shared_from_first - Riempie colonna per colonna
            // ===================================================================
            $firstLicense = $matrix[0]['worksMap'] ?? null;
            if (!$firstLicense) {
                foreach ($worksToAssign as $work) {
                    $this->addToUnassigned($work);
                }
                return;
            }

            $totalSlots = config('app_settings.matrix.total_slots', 25);
            $currentFixedSlot = null;

            // Trova il primo slot libero nella prima licenza
            for ($slot = 1; $slot <= $totalSlots; $slot++) {
                if (!isset($firstLicense[$slot]) || $firstLicense[$slot] === null) {
                    $currentFixedSlot = $slot;
                    break;
                }
            }

            if ($currentFixedSlot === null) {
                foreach ($worksToAssign as $work) {
                    $this->addToUnassigned($work);
                }
                return;
            }

            // Assegna i lavori colonna per colonna
            while (!$worksToAssign->isEmpty() && $currentFixedSlot <= $totalSlots) {
                // Per ogni colonna, scorre le licenze e assegna se possibile
                foreach ($matrix as $index => &$license) {
                    if ($worksToAssign->isEmpty()) {
                        break 2;
                    }

                    $nextWork = $worksToAssign->first();
                    $slotsNeeded = $nextWork['slots_occupied'] ?? 1;

                    // Controlla spazio consecutivo a partire da $currentFixedSlot
                    $canFit = true;
                    for ($i = 0; $i < $slotsNeeded; $i++) {
                        $checkSlot = $currentFixedSlot + $i;
                        if ($checkSlot > $totalSlots || !is_null($license['worksMap'][$checkSlot] ?? null)) {
                            $canFit = false;
                            break;
                        }
                    }

                    if (!$canFit) {
                        continue;
                    }

                    if (!$this->isAllowedToBeAdded($index, $nextWork)) {
                        continue;
                    }

                    if ($this->getCapacityLeft($index) < $slotsNeeded) {
                        continue;
                    }

                    // Assegna in blocco
                    for ($i = 0; $i < $slotsNeeded; $i++) {
                        $targetSlot = $currentFixedSlot + $i;
                        $license['worksMap'][$targetSlot] = $nextWork;
                    }

                    $this->saveMatrix($matrix);
                    $worksToAssign->shift();
                }

                // Passa alla colonna successiva se ci sono ancora lavori
                $currentFixedSlot++;
            }

            // Lavori rimanenti → unassigned
            foreach ($worksToAssign as $work) {
                $this->addToUnassigned($work);
            }

            return;
        }

        // ===================================================================
        // LOGICA NORMALE: colonna per colonna (slot 1 → 25)
        // ===================================================================
        $attempts = 0;
        $maxAttempts = $totalSlots * count($matrix); // Sicurezza

        while (!$worksToAssign->isEmpty() && $attempts < $maxAttempts) {
            $attempts++;

            $assignedInThisRound = false;

            // Scorro le colonne (slot 1 a 25)
            for ($currentSlot = 1; $currentSlot <= $totalSlots; $currentSlot++) {
                if ($worksToAssign->isEmpty()) {
                    break 2;
                }

                $nextWork = $worksToAssign->first();
                $slotsNeeded = $nextWork['slots_occupied'] ?? 1;

                // Scorro tutte le licenze per questa colonna
                foreach ($matrix as $index => &$license) {
                    if ($worksToAssign->isEmpty()) {
                        break 2;
                    }

                    // Controlla se c'è spazio consecutivo a partire da $currentSlot
                    $canFit = true;
                    for ($i = 0; $i < $slotsNeeded; $i++) {
                        $checkSlot = $currentSlot + $i;
                        if ($checkSlot > $totalSlots || !is_null($license['worksMap'][$checkSlot] ?? null)) {
                            $canFit = false;
                            break;
                        }
                    }

                    if (!$canFit) {
                        continue;
                    }

                    if (!$this->isAllowedToBeAdded($index, $nextWork)) {
                        continue;
                    }

                    if ($this->getCapacityLeft($index) < $slotsNeeded) {
                        continue;
                    }

                    // Assegna il lavoro a partire da $currentSlot
                    for ($i = 0; $i < $slotsNeeded; $i++) {
                        $targetSlot = $currentSlot + $i;
                        $license['worksMap'][$targetSlot] = $nextWork;
                    }

                    $this->saveMatrix($matrix);
                    $worksToAssign->shift();
                    $assignedInThisRound = true;

                    // Dopo aver assegnato, esci dal ciclo delle colonne per passare al prossimo lavoro
                    break 2;
                }
            }

            if (!$assignedInThisRound) {
                break; // Nessun progresso → esci
            }
        }

        // Lavori rimanenti → unassigned
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
            $slotsNeeded = $work['slots_occupied'] ?? 1; // <--- NUOVO: Ottiene gli slot richiesti

            // Cerca il blocco di slot liberi (USA LA NUOVA FUNZIONE)
            $startSlot = $this->findConsecutiveFreeSlots($license['worksMap'], $slotsNeeded);

            // Se non trova un blocco libero o la capacità residua non è sufficiente (Modificato)
            if ($startSlot === false || $this->getCapacityLeft($licenseId, true) < $slotsNeeded) {
                $this->addToUnassigned($work);
                continue;
            }

            // Assegna il lavoro a tutti gli slot occupati (NUOVO BLOCCO)
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $targetSlot = $startSlot + $i;
                $license['worksMap'][$targetSlot] = $work;
            }

            // Aggiorna il conteggio degli slot occupati in memoria (Fondamentale per capacityLeft)
            $license['slots_occupied'] = ($license['slots_occupied'] ?? 0) + $slotsNeeded;

            $this->matrix[$licenseId] = $license;
        }

        // Ripristina indici numerici (importante per round-robin)
        $this->matrix = $this->matrix->values();
    }

}
