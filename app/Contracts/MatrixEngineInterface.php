<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface MatrixEngineInterface
 *
 * @package App\Contracts
 *
 * Definisce le operazioni fondamentali per l'algoritmo di allocazione spaziale dei lavori.
 * Questo contratto permette di separare la logica di business dello smistamento (Splitter)
 * dalle specifiche implementazioni di posizionamento (Engine).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Interface Segregation: Isola le funzioni pure di calcolo spaziale (slot liberi, capacità)
 * dalla gestione dei modelli Eloquent.
 * 2. Strategy Pattern: Consente di cambiare l'algoritmo di distribuzione (es. da First-Fit a
 * Best-Fit) senza modificare i servizi che lo consumano.
 * 3. Spatial Validation: Garantisce che le implementazioni forniscano metodi sicuri per
 * identificare collisioni e continuità di slot.
 */

interface MatrixEngineInterface
{
    public function findConsecutiveFreeSlots(array $worksMap, int $slotsNeeded): int|false;

    public function distributeFixed(
        Collection $worksToAssign,
        Collection &$matrix,
        Collection &$unassigned,
        Collection $allWorks
    ): void;

    public function distribute(
        Collection $worksToAssign,
        Collection &$matrix,
        Collection &$unassigned,
        Collection $allWorks,
        bool $useFirstSlotOnly = false
    ): void;

    public function getCapacityLeft(
        array $license,
        Collection $allWorks,
        bool $useTargetLimit = true
    ): int;

    public function sortMatrixRows(Collection &$matrix): void;
}
