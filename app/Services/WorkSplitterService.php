<?php

namespace App\Services;

use App\Models\{LicenseTable, WorkAssignment};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class WorkSplitterService
{
    private const MAX_SLOTS = 25;
    private const CASH_AMOUNT = 90; // Importo fisso per X

    /** @var array<int, array<int, WorkAssignment|stdClass|null>> Matrice [LicenseTableId][Slot] */
    private array $grid = [];

    /** @var Collection<int, WorkAssignment> Tutti i lavori di oggi non esclusi. */
    private Collection $sharableWorks;

    /** @var Collection<int, LicenseTable> Le licenze attive di oggi, ordinate. */
    private Collection $licenses;

    /** @var array<int> ID delle LicenseTable da escludere dalla ripartizione dei lavori A. */
    private array $excludedFromAIds;

    /**
     * Costruttore: accetta la lista degli ID delle licenze da escludere dai lavori A.
     */
    public function __construct(Collection $licenses, Collection $sharableWorks, array $excludedFromAIds)
    {
        $this->licenses = $licenses;
        $this->sharableWorks = $sharableWorks;
        $this->excludedFromAIds = $excludedFromAIds;
        $this->initializeGrid();
    }

    /**
     * Inizializza la griglia [LicenseTableId][Slot] a null e popola i lavori esclusi (Punto 1 iniziale).
     */
    private function initializeGrid(): void
    {
        foreach ($this->licenses as $license) {
            $this->grid[$license->id] = array_fill(1, self::MAX_SLOTS, null);
        }

        // 1) Popolare la tabella con i lavori fissi (excluded == 1)
        $excludedWorks = WorkAssignment::whereDate('timestamp', today())
            ->where('excluded', true)
            ->get();

        foreach ($excludedWorks as $work) {
            if (isset($this->grid[$work->license_table_id])) {
                for ($s = 0; $s < $work->slots_occupied; $s++) {
                    $slot = $work->slot + $s;
                    if ($slot <= self::MAX_SLOTS) {
                        // Salva solo il record principale nel primo slot (s==0)
                        $this->grid[$work->license_table_id][$slot] = ($s === 0)
                            ? $work
                            : $this->getPlaceholderWork($work);
                    }
                }
            }
        }
    }

    /**
     * Restituisce un oggetto placeholder per gli slot occupati da un blocco multi-slot.
     */
    private function getPlaceholderWork(WorkAssignment $mainWork): stdClass
    {
        $placeholder = new stdClass();
        $placeholder->value = $mainWork->value;
        $placeholder->slots_occupied = 0;
        $placeholder->slot = 0;
        return $placeholder;
    }

    /**
     * Controlla se uno slot è disponibile.
     * Gestisce la logica di esclusione per i lavori A (Punto 1).
     */
    private function isSlotAvailable(int $licenseTableId, int $slot, string $workType = null): bool
    {
        // Check 1: Lo slot è già occupato da un lavoro (fisso o ripartito)
        if (isset($this->grid[$licenseTableId][$slot]) && $this->grid[$licenseTableId][$slot] !== null) {
            return false;
        }

        // Check 2: Se stiamo distribuendo lavori A, e la licenza è esclusa (Punto 1)
        if ($workType === 'A' && in_array($licenseTableId, $this->excludedFromAIds)) {
            // Lo slot è libero, ma deve essere trattato come "occupato" per la ripartizione A.
            return false;
        }

        return true; // Slot libero
    }

    /**
     * Assegna un lavoro alla griglia e ne marca gli slot occupati.
     */
    private function assignWorkToGrid(int $licenseTableId, int $slot, WorkAssignment $work): void
    {
        $slotsOccupied = $work->slots_occupied;
        $work->slot = $slot;
        $work->license_table_id = $licenseTableId;

        for ($s = 0; $s < $slotsOccupied; $s++) {
            $currentSlot = $slot + $s;
            if ($currentSlot <= self::MAX_SLOTS) {
                // Salva il record principale solo nel primo slot
                $this->grid[$licenseTableId][$currentSlot] = ($s === 0)
                    ? $work
                    : $this->getPlaceholderWork($work);
            }
        }
    }

    /**
     * Ripartisce i lavori Agency (A) e poi i lavori Cash (X, P, N) in modo equo tra le licenze.
     */
    private function distributeSharableWorks(string $valueFilter): void
    {
        $worksToDistribute = $this->sharableWorks
            ->where('value', $valueFilter)
            ->where('shared_from_first', false) // Esclude i lavori SFF gestiti a parte
            ->values();

        $licenseIndex = 0;
        $slot = 1;

        foreach ($worksToDistribute as $work) {
            $assigned = false;

            while (!$assigned && $slot <= self::MAX_SLOTS) {
                $license = $this->licenses->get($licenseIndex);
                if (!$license) {
                    // Passa allo slot successivo e ricomincia dalla prima licenza
                    $licenseIndex = 0;
                    $slot++;
                    continue;
                }

                $licenseTableId = $license->id;
                $slotsNeeded = $work->slots_occupied;

                // Trova il primo blocco libero di $slotsNeeded
                $isFree = true;
                $startSlot = $slot;
                for ($i = 0; $i < $slotsNeeded; $i++) {
                    // Passa il tipo di lavoro per gestire l'esclusione A (Punto 1)
                    if ($startSlot + $i > self::MAX_SLOTS || !$this->isSlotAvailable($licenseTableId, $startSlot + $i, $valueFilter)) {
                        $isFree = false;
                        break;
                    }
                }

                if ($isFree) {
                    $this->assignWorkToGrid($licenseTableId, $startSlot, $work);
                    $assigned = true;
                }

                $licenseIndex++; // Passa alla licenza successiva per la ripartizione
            }
        }
    }

    /**
     * Distribuisce i lavori 'Shared From First' (SFF) tra tutte le licenze (Punto 2).
     * La logica è simile a distributeSharableWorks, ma opera solo sui lavori SFF.
     */
    private function distributeSharedFromFirstWorks(): void
    {
        $worksToDistribute = $this->sharableWorks->where('shared_from_first', true)->values();

        if ($worksToDistribute->isEmpty() || $this->licenses->isEmpty()) return;

        // 1. TROVA IL PUNTO DI PARTENZA (Primo slot libero per la PRIMA licenza)
        $startSlot = self::MAX_SLOTS + 1; // Default: nessun slot libero
        $firstLicense = $this->licenses->first();
        $firstLicenseId = $firstLicense->id;

        for ($s = 1; $s <= self::MAX_SLOTS; $s++) {
            // Usiamo isSlotAvailable() senza workType (non è A e non vogliamo attivare l'esclusione)
            if ($this->isSlotAvailable($firstLicenseId, $s)) {
                $startSlot = $s;
                break;
            }
        }

        // Se non trova un punto di partenza (prima licenza satura), interrompi
        if ($startSlot > self::MAX_SLOTS) return;

        // 2. RIPARTIZIONE (Turnazione)
        $licenseIndex = 0;       // Iniziamo dalla prima licenza
        $slot = $startSlot;      // Iniziamo dallo slot libero trovato

        foreach ($worksToDistribute as $work) {
            $assigned = false;

            while (!$assigned && $slot <= self::MAX_SLOTS) {
                $license = $this->licenses->get($licenseIndex);
                if (!$license) {
                    // Passa allo slot successivo e ricomincia dalla prima licenza
                    $licenseIndex = 0;
                    $slot++;
                    continue;
                }

                $licenseTableId = $license->id;
                $slotsNeeded = $work->slots_occupied;

                // Cerca il blocco libero
                $isFree = true;
                $startBlockSlot = $slot;

                for ($i = 0; $i < $slotsNeeded; $i++) {
                    // Controllo di disponibilità generico
                    if ($startBlockSlot + $i > self::MAX_SLOTS || !$this->isSlotAvailable($licenseTableId, $startBlockSlot + $i)) {
                        $isFree = false;
                        break;
                    }
                }

                if ($isFree) {
                    $this->assignWorkToGrid($licenseTableId, $startBlockSlot, $work);
                    $assigned = true;
                }

                $licenseIndex++; // Passa alla licenza successiva per la turnazione
            }

            // Se un lavoro è assegnato, la prossima iterazione (prossimo lavoro)
            // riparte dallo stesso slot ma dalla licenza successiva, mantenendo la turnazione.
        }
    }

    /**
     * Sostituisce i lavori generici (X) con quelli specifici (N/P) partendo dalla fine.
     */
    private function applySpecificWorks(string $valueFilter): void
    {
        $specificWorksByLicense = $this->sharableWorks
            ->where('value', $valueFilter)
            ->groupBy('license_table_id');

        foreach ($this->licenses as $license) {
            $licenseId = $license->id;

            // Totale slot (N/P) eseguiti da questa licenza
            $totalSpecificSlots = $specificWorksByLicense->get($licenseId, collect())->sum('slots_occupied');
            if ($totalSpecificSlots === 0) continue;

            $slotsRemainingToReplace = $totalSpecificSlots;

            // Partiamo dalla fine e sostituiamo le X con N/P
            for ($slot = self::MAX_SLOTS; $slot >= 1; $slot--) {
                $work = $this->grid[$licenseId][$slot];

                // Cerchiamo un lavoro ripartito (X) che sia il "capo" del suo blocco (work->slot === $slot)
                if ($work instanceof WorkAssignment && $work->value === 'X' && $work->slot === $slot) {

                    $slotsInCurrentXBlock = $work->slots_occupied;
                    $slotsToUse = min($slotsInCurrentXBlock, $slotsRemainingToReplace);

                    if ($slotsToUse > 0) {
                        // Creiamo il nuovo lavoro di tipo N/P (con lo stesso slot di partenza)
                        $newWork = $work->replicate();
                        $newWork->value = $valueFilter;
                        $newWork->slots_occupied = $slotsToUse;

                        // Riassegna il blocco come N/P
                        $this->assignWorkToGrid($licenseId, $slot, $newWork);

                        $slotsRemainingToReplace -= $slotsToUse;
                    }

                    if ($slotsRemainingToReplace === 0) break;
                }
            }
        }
    }

    /**
     * Calcola i riepiloghi (Punto 7).
     */
    private function calculateSummary(float $bancaleCost = 0.0): array
    {
        $finalTable = [];

        foreach ($this->licenses as $license) {
            $licenseId = $license->id;
            $cashCount = 0;
            $nCounts = 0;
            $pCounts = 0;

            foreach ($this->grid[$licenseId] as $slot => $work) {
                // Contiamo solo il record principale (quello che ha work->slot === $slot e non è stdClass)
                if ($work instanceof WorkAssignment && $work->slot === $slot) {
                    if ($work->value === 'X') {
                        $cashCount += $work->slots_occupied;
                    } elseif ($work->value === 'N') {
                        $nCounts += $work->slots_occupied;
                    } elseif ($work->value === 'P') {
                        $pCounts += $work->slots_occupied;
                    }
                }
            }

            // (nr X x 90) - costo bancale
            $totalCashDue = ($cashCount * self::CASH_AMOUNT) - $bancaleCost;

            $finalTable[] = [
                'license_table_id' => $licenseId, // Importante per la UI di esclusione
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


    /**
     * METODO PUBBLICO PRINCIPALE: Workflow di Ripartizione
     */
    public function getSplitTable(float $bancaleCost = 0.0): array
    {
        // Lavori fissi (excluded=1) già gestiti in initializeGrid()

        // 1. Distribuzione Agency (A) - Rispettando l'esclusione (Punto 1)
        $this->distributeSharableWorks('A');

        // 2. Distribuzione Shared From First (SFF) (Punto 2)
        $this->distributeSharedFromFirstWorks();

        // 3. Distribuzione Cash (X, N, P) - Riempimento generico come X
        // Distribuiamo tutti i lavori Cash non assegnati come X generici per essere sostituiti dopo.
        $this->distributeSharableWorks('X');
        $this->distributeSharableWorks('N');
        $this->distributeSharableWorks('P');

        // 4. Sostituzione N e P (Punti 4 e 5)
        $this->applySpecificWorks('N');
        $this->applySpecificWorks('P');

        // 5. Calcoli riepilogativi
        return $this->calculateSummary($bancaleCost);
    }
}
