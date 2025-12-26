<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

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