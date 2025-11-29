<?php

// app/Traits/DistributesWorksToMatrix.php

namespace App\Traits;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait DistributesWorksToMatrix
{
    /**
     * Distribuisce i lavori in round-robin rispettando:
     * - turno mezza giornata (morning/afternoon)
     * - capacità reale della licenza (real_slots_today)
     * - primo slot libero disponibile
     * - round-robin perfetto (ricomincia dalla prima)
     */
    /**
 * VERSIONE DEFINITIVA – GOLD STANDARD
 * 1. Trova il primo slot libero in tutta la matrice (scansione naturale)
 * 2. Da lì parte un round-robin perfetto tra le licenze
 * → Compatta dall'alto, poi bilancia equamente
 */
public function distributeWorks(array|Collection $pendingWorks): void
{
    $works = collect($pendingWorks)->values();
    if ($works->isEmpty()) {
        return;
    }

    $this->ensureMatrixIsReady();

    // === FASE 1: Trova da dove partire (primo buco libero) ===
    $startLicenseIndex = 0;
    $startSlot = 0;
    $foundStartingPoint = false;

    foreach ($this->matrix as $licenseIndex => $license) {
        if (!is_array($license) || !isset($license['worksMap'])) {
            continue;
        }

        foreach ($license['worksMap'] as $slot => $value) {
            if ($value === null) {
                $startLicenseIndex = $licenseIndex;
                $startSlot = $slot;
                $foundStartingPoint = true;
                break 2; // esce da entrambi i foreach
            }
        }
    }

    // Se non ci sono buchi → niente da fare
    if (!$foundStartingPoint) {
        return;
    }

    // === FASE 2: Round-robin partendo dal buco trovato ===
    $totalLicenses = $this->matrix->count();
    $currentIndex = $startLicenseIndex;

    foreach ($works as $work) {
        $assigned = false;
        $attempts = 0;
        $maxAttempts = $totalLicenses * 2; // sicurezza

        while ($attempts++ < $maxAttempts && !$assigned) {
            $licenseIndex = $currentIndex % $totalLicenses;
            $license = $this->matrix->get($licenseIndex);

            if (!is_array($license) || !isset($license['worksMap'])) {
                $currentIndex++;
                continue;
            }

            if (!$this->canAcceptWork($license, $work)) {
                $currentIndex++;
                continue;
            }

            // IMPORTANTE: riprendi dal primo slot libero (non dal primo assoluto)
            $slot = $this->findFirstFreeSlot($license['worksMap']);
            if ($slot !== false) {
                $this->placeWorkInSlot($licenseIndex, $slot, $work, $license['id']);
                $assigned = true;
            }

            $currentIndex++; // sempre avanti, anche se non assegnato (per evitare blocchi)
        }

        if (!$assigned) {
            Log::warning('Work could not be assigned - no compatible slot found', [
                'work_id' => $work['id'] ?? null,
                'value'   => $work['value'] ?? 'unknown',
                'time'    => $work['timestamp'] ?? null,
            ]);
        }
    }
}

    /**
     * Prova ad assegnare un singolo lavoro.
     * Ritorna true se assegnato, false se nessuna licenza compatibile.
     */
    private function tryAssignWork(array $work, int $totalLicenses, int &$currentIndex): bool
    {
        $attempts = 0;
        $maxAttempts = $totalLicenses * 2; // massimo 2 giri completi

        while ($attempts++ < $maxAttempts) {
            $licenseIndex = $currentIndex % $totalLicenses;
            $license = $this->matrix->get($licenseIndex);

            // Protezione forte per Intelephense e sicurezza runtime
            if (! is_array($license) || ! isset($license['worksMap']) || ! is_array($license['worksMap'])) {
                $currentIndex++;

                continue;
            }

            if (! $this->canAcceptWork($license, $work)) {
                $currentIndex++;

                continue;
            }

            $slot = $this->findFirstFreeSlot($license['worksMap']);
            if ($slot === false) {
                $currentIndex++;

                continue;
            }

            $this->placeWorkInSlot($licenseIndex, $slot, $work, $license['id']);

            return true;
        }

        return false;
    }

    /**
     * Verifica se la licenza può accettare questo lavoro
     */
    private function canAcceptWork(array $license, array $work): bool
    {
        // 1. Deve avere worksMap valido
        if (! isset($license['worksMap']) || ! is_array($license['worksMap'])) {
            return false;
        }

        // 2. Rispetto turno mezza giornata
        $shift = $license['shift'] ?? 'full';
        if (in_array($shift, ['morning', 'afternoon'], true)) {
            $workTime = $this->extractWorkTime($work);

            if ($shift === 'morning' && $workTime > '13:00') {
                return false;
            }
            if ($shift === 'afternoon' && $workTime < '13:31') {
                return false;
            }
        }

        // 3. Rispetto capacità reale
        $capacity = $license['real_slots_today'] ?? 25;
        $alreadyUsed = count(array_filter($license['worksMap'], fn ($item) => $item !== null));

        if ($alreadyUsed >= $capacity) {
            return false;
        }

        return true;
    }

    /**
     * Trova il primo slot libero (0–24)
     */
    private function findFirstFreeSlot(array $worksMap): int|false
    {
        foreach ($worksMap as $slot => $value) {
            if ($value === null) {
                return $slot;
            }
        }

        return false;
    }

    /**
     * Assegna il lavoro nello slot (100% Collection-safe)
     */
    private function placeWorkInSlot(int $licenseIndex, int $slot, array $work, int $licenseId): void
    {
        $workData = $work;
        $workData['license_table_id'] = $licenseId;

        $license = $this->matrix->get($licenseIndex);
        $license['worksMap'][$slot] = $workData;

        $this->matrix->put($licenseIndex, $license);
    }

    /**
     * Estrae l'orario HH:ii dal timestamp del lavoro
     */
    private function extractWorkTime(array $work): string
    {
        $ts = $work['timestamp'] ?? null;

        if ($ts instanceof DateTimeInterface) {
            return $ts->format('H:i');
        }

        if (is_string($ts) && strlen($ts) >= 19) {
            return substr($ts, 11, 5); // "14:30"
        }

        return '00:00';
    }

    /**
     * Normalizza $this->matrix in una Collection indicizzata 0,1,2...
     */
    private function ensureMatrixIsReady(): void
    {
        if (! isset($this->matrix)) {
            throw new RuntimeException('$this->matrix non è inizializzata in '.static::class);
        }

        if ($this->matrix instanceof Collection) {
            $this->matrix = $this->matrix->values();
        } elseif (is_array($this->matrix)) {
            $this->matrix = collect(array_values($this->matrix));
        } else {
            throw new RuntimeException('$this->matrix deve essere un array o Collection');
        }

        if ($this->matrix->isEmpty()) {
            throw new RuntimeException('$this->matrix è vuota – licenseTable non valido');
        }
    }

    // Aggiungi questo metodo dentro HasWorkQueries.php

    /**
     * Assegna i lavori fissi (excluded = true) alla loro licenza originale,
     * ma nel primo slot libero disponibile (non necessariamente nella posizione originale)
     */
    public function assignFixedWorks(): void
    {
        $fixedWorks = $this->allWorks()->where('excluded', true);

        foreach ($fixedWorks as $work) {
            $originalLicenseId = $work['license_table_id'] ?? null;

            if (! $originalLicenseId) {
                continue; // non dovrebbe succedere
            }

            // Trova la riga nella matrice con questa licenza
            $licenseRow = $this->matrix->firstWhere('id', $originalLicenseId);

            if (! $licenseRow || ! is_array($licenseRow['worksMap'])) {
                continue;
            }

            // Trova il primo slot libero
            $freeSlot = $this->findFirstFreeSlot($licenseRow['worksMap']);

            if ($freeSlot !== false) {
                // Copia il lavoro (con license_table_id corretto)
                $workData = $work;
                $workData['license_table_id'] = $originalLicenseId;

                // Assegna nello slot libero
                $licenseRow['worksMap'][$freeSlot] = $workData;

                // Aggiorna la matrice
                $index = $this->matrix->search(fn ($row) => ($row['id'] ?? null) === $originalLicenseId);
                if ($index !== false) {
                    $this->matrix->put($index, $licenseRow);
                }
            }
        }
    }
}
