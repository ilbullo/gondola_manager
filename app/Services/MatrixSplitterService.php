<?php

namespace App\Services;

use App\Traits\{HasWorkQueries, MatrixDistribution};

class MatrixSplitterService
{
    use HasWorkQueries;           // ← filtra e prepara
    use MatrixDistribution; // ← assegna fisicamente nella matrice

    public $licenseTable = [];
    public $matrix;
    public $unassignedWorks;

    public function __construct($licenseTable)
    {
        // $licenseTable è un array o Collection di licenze con:
        // id, user, shift, real_slots_today, worksMap (opzionale)
        $this->licenseTable = $licenseTable instanceof \Illuminate\Support\Collection
            ? $licenseTable->toArray()
            : $licenseTable;

        $this->unassignedWorks = collect($this->unassignedWorks ?? []);

        $this->prepareMatrix();                    // crea matrice vuota + copia id/user/shift
        $this->distributeFixed($this->fixedAgencyWorks()->values());
        $this->distribute($this->pendingMorningAgencyWorks()->values());
        $this->distribute($this->pendingAfternoonAgencyWorks()->values());
        $this->distribute($this->sharableFirstWorks()->values(),true);
        $this->distribute($this->pendingCashWorks()); 
        $this->distributeFixed($this->pendingNPWorks());
        //dump($this->unassignedWorks);
        //dump($this->debugInfo());
        //dump($this->allWorks());
    }

}