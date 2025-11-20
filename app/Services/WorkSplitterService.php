<?php

namespace App\Services;

use App\Enums\DayType;
use App\Models\{LicenseTable, WorkAssignment};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use Carbon\Carbon;

class WorkSplitterService
{
    private const MAX_SLOTS = 25;
    private const CASH_AMOUNT = 90;
    
    // Costanti orarie
    private const MORNING_END_TIME = '13:30'; 
    private const AFTERNOON_START_TIME = '13:31';

    private array $grid = [];
    private Collection $sharableWorks;
    private Collection $licenses;
    private array $excludedFromAIds;
    private array $shifts; 

    // NUOVO: Mappa per gestire i limiti di capacità
    /** @var array<int, int> Mappa [LicenseId => TotaleSlotSvoltiReali] */
    private array $licenseCapacities = [];

    /** @var array<int, int> Mappa [LicenseId => TotaleSlotAssegnatiAttuali] */
    private array $currentLoad = [];

    public function __construct(
        Collection $licenses, 
        Collection $sharableWorks, 
        array $excludedFromAIds, 
        array $shifts
    ) {
        $this->licenses = $licenses;
        $this->sharableWorks = $sharableWorks;
        $this->excludedFromAIds = $excludedFromAIds;
        $this->shifts = $shifts;

        // 1. Calcola il limite massimo per ogni licenza basandosi sui lavori reali
        $this->calculateLicenseCapacities();
        
        // 2. Inizializza la griglia
        $this->initializeGrid();
    }

    /**
     * Calcola il numero totale di slot che ogni licenza ha effettivamente lavorato oggi.
     * Questo numero funge da "tetto massimo" per le assegnazioni.
     */
    private function calculateLicenseCapacities(): void
    {
        // Inizializza tutto a 0 e il carico attuale a 0
        foreach ($this->licenses as $license) {
            $this->licenseCapacities[$license->id] = 0;
            $this->currentLoad[$license->id] = 0;
        }

        // Query per contare i lavori reali nel DB per oggi (inclusi excluded, shared, ecc.)
        $actualWorkCounts = WorkAssignment::whereDate('timestamp', today())
            ->select('license_table_id', DB::raw('SUM(slots_occupied) as total_slots'))
            ->groupBy('license_table_id')
            ->pluck('total_slots', 'license_table_id');

        foreach ($actualWorkCounts as $licenseId => $count) {
            if (isset($this->licenseCapacities[$licenseId])) {
                $this->licenseCapacities[$licenseId] = (int)$count;
            }
        }
    }

    /**
     * Verifica se la licenza ha spazio nel suo "Cap" personale per accettare nuovi slot.
     */
    private function hasCapacity(int $licenseId, int $slotsNeeded): bool
    {
        $current = $this->currentLoad[$licenseId] ?? 0;
        $max = $this->licenseCapacities[$licenseId] ?? 0;

        return ($current + $slotsNeeded) <= $max;
    }

    private function initializeGrid(): void
    {
        foreach ($this->licenses as $license) {
            $this->grid[$license->id] = array_fill(1, self::MAX_SLOTS, null);
        }

        $excludedWorks = WorkAssignment::whereDate('timestamp', today())
            ->where('excluded', true)
            ->get();

        foreach ($excludedWorks as $work) {
            if (isset($this->grid[$work->license_table_id])) {
                $this->assignWorkToGrid($work->license_table_id, $work->slot, $work);
            }
        }
    }

    private function getPlaceholderWork(WorkAssignment $mainWork): stdClass
    {
        $placeholder = new stdClass();
        $placeholder->value = $mainWork->value;
        $placeholder->slots_occupied = 0;
        $placeholder->slot = 0;
        return $placeholder;
    }

    private function isWorkCompatibleWithShift(int $licenseId, WorkAssignment $work): bool
    {
        $shift = DayType::tryFrom($this->shifts[$licenseId] ?? 'full');
        
        if ($shift === DayType::FULL) return true;

        $workTime = $work->timestamp instanceof Carbon 
            ? $work->timestamp->format('H:i') 
            : Carbon::parse($work->timestamp)->format('H:i');

        if ($shift === DayType::MORNING) {
            return $workTime <= self::MORNING_END_TIME;
        }

        if ($shift === DayType::AFTERNOON) {
            return $workTime >= self::AFTERNOON_START_TIME;
        }

        return true;
    }

    private function isSlotAvailable(int $licenseTableId, int $slot, string $workType = null): bool
    {
        if (isset($this->grid[$licenseTableId][$slot]) && $this->grid[$licenseTableId][$slot] !== null) {
            return false;
        }

        if ($workType === 'A' && in_array($licenseTableId, $this->excludedFromAIds)) {
            return false;
        }

        return true;
    }

    private function assignWorkToGrid(int $licenseTableId, int $slot, WorkAssignment $work): void
    {
        // Aggiorna il carico attuale (Current Load)
        // Nota: Se stiamo sovrascrivendo (es. replace X con N), dovremmo gestire la differenza,
        // ma in questo algoritmo "restoreSpecificLicenseWorks" sostituisce 1 a 1, quindi il carico totale non cambia.
        // Tuttavia, per le assegnazioni iniziali (initializeGrid e distribute) è fondamentale incrementare.
        
        // Per evitare doppi conteggi durante il "replace" (sovrascrittura), controlliamo se lo slot era vuoto.
        // Se lo slot era già occupato (sovrascrittura), non aumentiamo il carico (è uno scambio).
        $isOverwrite = isset($this->grid[$licenseTableId][$slot]) && $this->grid[$licenseTableId][$slot] !== null;
        
        if (!$isOverwrite) {
            $this->currentLoad[$licenseTableId] = ($this->currentLoad[$licenseTableId] ?? 0) + $work->slots_occupied;
        }

        $clonedWork = $work->replicate();
        $clonedWork->id = $work->id;
        $clonedWork->setRelation('agency', $work->agency);
        
        $clonedWork->slot = $slot;
        $clonedWork->license_table_id = $licenseTableId;
        $clonedWork->slots_occupied = $work->slots_occupied;
        $clonedWork->timestamp = $work->timestamp;

        $slotsOccupied = $work->slots_occupied;

        for ($s = 0; $s < $slotsOccupied; $s++) {
            $currentSlot = $slot + $s;
            if ($currentSlot <= self::MAX_SLOTS) {
                $this->grid[$licenseTableId][$currentSlot] = ($s === 0)
                    ? $clonedWork
                    : $this->getPlaceholderWork($clonedWork);
            }
        }
    }

    private function distributeSharableWorks(string $valueFilter, ?Collection $worksToDistributeOverride = null): void
    {
        $worksToDistribute = $worksToDistributeOverride 
            ? $worksToDistributeOverride->values()
            : $this->sharableWorks
                ->where('value', $valueFilter)
                ->where('shared_from_first', false)
                ->values();

        $licenseIndex = 0;
        $slot = 1;
        $totalLicenses = $this->licenses->count();

        foreach ($worksToDistribute as $work) {
            $assigned = false;
            $attempts = 0;

            while (!$assigned && $slot <= self::MAX_SLOTS && $attempts < ($totalLicenses * self::MAX_SLOTS)) {
                $license = $this->licenses->get($licenseIndex);
                
                if (!$license) {
                    $licenseIndex = 0;
                    $slot++;
                    continue; 
                }

                // 1. CHECK COMPATIBILITÀ TURNO
                if (!$this->isWorkCompatibleWithShift($license->id, $work)) {
                    $licenseIndex++;
                    if ($licenseIndex >= $totalLicenses) { $licenseIndex = 0; $slot++; }
                    $attempts++;
                    continue;
                }

                // 2. NUOVO: CHECK CAPACITÀ MASSIMA (Se ha già fatto troppi lavori, salta)
                if (!$this->hasCapacity($license->id, $work->slots_occupied)) {
                     $licenseIndex++;
                     if ($licenseIndex >= $totalLicenses) { $licenseIndex = 0; $slot++; }
                     $attempts++;
                     continue;
                }

                $licenseTableId = $license->id;
                $slotsNeeded = $work->slots_occupied;

                $isFree = true;
                $startSlot = $slot;
                for ($i = 0; $i < $slotsNeeded; $i++) {
                    if ($startSlot + $i > self::MAX_SLOTS || !$this->isSlotAvailable($licenseTableId, $startSlot + $i, $work->value)) {
                        $isFree = false;
                        break;
                    }
                }

                if ($isFree) {
                    $this->assignWorkToGrid($licenseTableId, $startSlot, $work);
                    $assigned = true;
                }

                $licenseIndex++;
                $attempts++;
            }
        }
    }

    private function distributeSharedFromFirstWorks(): void
    {
         $worksToDistribute = $this->sharableWorks->where('shared_from_first', true)->values();
         if ($worksToDistribute->isEmpty() || $this->licenses->isEmpty()) return;

         $startSlot = self::MAX_SLOTS + 1;
         $firstLicense = $this->licenses->first();
         
         for ($s = 1; $s <= self::MAX_SLOTS; $s++) {
             if ($this->isSlotAvailable($firstLicense->id, $s)) {
                 $startSlot = $s;
                 break;
             }
         }
         if ($startSlot > self::MAX_SLOTS) return;

         $licenseIndex = 0;
         $slot = $startSlot;
         $totalLicenses = $this->licenses->count();

         foreach ($worksToDistribute as $work) {
             $assigned = false;
             $attempts = 0;

             while (!$assigned && $slot <= self::MAX_SLOTS && $attempts < ($totalLicenses * self::MAX_SLOTS)) {
                 $license = $this->licenses->get($licenseIndex);
                 if (!$license) {
                     $licenseIndex = 0;
                     $slot++;
                     continue;
                 }
                 
                 // Check Turno
                 if (!$this->isWorkCompatibleWithShift($license->id, $work)) {
                     $licenseIndex++;
                     $attempts++;
                     continue;
                 }
                 
                 // NUOVO: Check Capacità
                 if (!$this->hasCapacity($license->id, $work->slots_occupied)) {
                     $licenseIndex++;
                     $attempts++;
                     continue;
                 }

                 $slotsNeeded = $work->slots_occupied;
                 $isFree = true;
                 for ($i = 0; $i < $slotsNeeded; $i++) {
                     if ($slot + $i > self::MAX_SLOTS || !$this->isSlotAvailable($license->id, $slot + $i)) {
                         $isFree = false;
                         break;
                     }
                 }

                 if ($isFree) {
                     $this->assignWorkToGrid($license->id, $slot, $work);
                     $assigned = true;
                 }
                 $licenseIndex++;
                 $attempts++;
             }
         }
    }

    private function restoreSpecificLicenseWorks(): void
    {
        $specificWorksByLicense = $this->sharableWorks
            ->whereIn('value', ['N', 'P'])
            ->groupBy('license_table_id');

        foreach ($this->licenses as $license) {
            $licenseId = $license->id;
            
            $mySpecificWorks = $specificWorksByLicense->get($licenseId);

            if (!$mySpecificWorks || $mySpecificWorks->isEmpty()) {
                continue;
            }

            $worksQueue = $mySpecificWorks->all(); 

            for ($slot = self::MAX_SLOTS; $slot >= 1; $slot--) {
                
                if (empty($worksQueue)) break;

                $currentWork = $this->grid[$licenseId][$slot] ?? null;

                // Qui stiamo SOVRASCRIVENDO una X con N/P.
                // Poiché 1 X = 1 Slot e 1 N = 1 Slot, il currentLoad non cambia.
                // Non serve controllare hasCapacity qui perché stiamo solo scambiando etichette.
                if ($currentWork instanceof WorkAssignment 
                    && $currentWork->slot === $slot 
                    && $currentWork->value === 'X') {
                    
                    $workToRestore = array_pop($worksQueue); 

                    $newWork = $workToRestore->replicate();
                    $newWork->id = $workToRestore->id;
                    $newWork->value = $workToRestore->value; 
                    $newWork->slots_occupied = 1; 
                    
                    $this->assignWorkToGrid($licenseId, $slot, $newWork);
                }
            }
        }
    }

    private function calculateSummary(float $bancaleCost = 0.0): array
    {
        $finalTable = [];

        foreach ($this->licenses as $license) {
            $licenseId = $license->id;
            $cashDue = 0; 
            $nCounts = 0;
            $pCounts = 0;

            foreach ($this->grid[$licenseId] as $slot => $work) {
                if ($work instanceof WorkAssignment && $work->slot === $slot) {
                    if ($work->value === 'N') $nCounts += $work->slots_occupied;
                    if ($work->value === 'P') $pCounts += $work->slots_occupied;

                    if (in_array($work->value, ['X', 'N', 'P'])) {
                        $cashDue += ($work->slots_occupied * self::CASH_AMOUNT);
                    }
                }
            }

            $totalCashDue = $cashDue - $bancaleCost;

            $finalTable[] = [
                'license_table_id' => $licenseId,
                'license'       => $license->user->license_number,
                'user_name'     => trim("{$license->user->name} {$license->user->surname}"),
                'cash_due'      => $totalCashDue,
                'n_count'       => $nCounts,
                'p_count'       => $pCounts,
                'assignments'   => $this->grid[$licenseId],
            ];
        }

        return $finalTable;
    }

    public function getValidationStats(): array
    {
        // Logica di validazione invariata
        $expected = ['A' => 0, 'X' => 0, 'N' => 0, 'P' => 0];
        
        foreach ($this->sharableWorks as $work) {
            if (isset($expected[$work->value])) {
                $expected[$work->value] += $work->slots_occupied;
            }
        }
        
        $fixedWorks = WorkAssignment::whereDate('timestamp', today())
            ->where('excluded', true)
            ->get();
        foreach ($fixedWorks as $work) {
            if (isset($expected[$work->value])) {
                $expected[$work->value] += $work->slots_occupied;
            }
        }

        $actual = ['A' => 0, 'X' => 0, 'N' => 0, 'P' => 0];

        foreach ($this->grid as $licenseId => $slots) {
            foreach ($slots as $slot => $work) {
                if ($work instanceof WorkAssignment && $work->slot === $slot) {
                    if (isset($actual[$work->value])) {
                        $actual[$work->value] += $work->slots_occupied;
                    }
                }
            }
        }

        $diff = [];
        $hasDiscrepancy = false;
        foreach ($expected as $type => $count) {
            $diff[$type] = $actual[$type] - $count;
            if ($diff[$type] !== 0) $hasDiscrepancy = true;
        }

        return [
            'expected' => $expected,
            'actual'   => $actual,
            'diff'     => $diff,
            'hasDiscrepancy' => $hasDiscrepancy
        ];
    }

    public function getSplitTable(float $bancaleCost = 0.0): array
    {
        $this->distributeSharableWorks('A');

        $this->distributeSharedFromFirstWorks();

        // 3. Aggregazione Cash (X + N + P) come X
        $aggregatedCashWorks = $this->sharableWorks
            ->filter(fn($w) => in_array($w->value, ['X', 'N', 'P']))
            ->map(function($work) {
                if (in_array($work->value, ['N', 'P'])) {
                    $clone = $work->replicate();
                    $clone->id = $work->id; 
                    $clone->value = 'X'; 
                    $clone->timestamp = $work->timestamp; 
                    $clone->slots_occupied = $work->slots_occupied;
                    return $clone;
                }
                return $work;
            });

        $this->distributeSharableWorks('X', $aggregatedCashWorks);

        $this->restoreSpecificLicenseWorks();

        return $this->calculateSummary($bancaleCost);
    }
}