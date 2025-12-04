<?php

namespace App\Traits;

use App\Enums\DayType;
use App\Enums\WorkType;
use Illuminate\Support\Collection;
use RuntimeException;
use DateTimeInterface;


trait MatrixDistribution {

    private function getMatrix() {
        return $this->matrix->toArray();
    }

    private function saveMatrix($matrix) {
        $this->matrix = collect($matrix);
    }

    private function countFixedWorks($key) : int 
{
    $original = $this->licenseTable[$key]; // matrice originale

    if (!$original) { 
        throw new RuntimeException('Tabella inesistente');
    }

    return collect($original['worksMap'])
        ->filter(function ($work) {
            return $work &&
                (   
                    //controllo il numero di N e P e lavori esclusi 
                    in_array($work['value'], ['N', 'P']) ||
                    (($work['excluded'] ?? false) === true && $work['value'] != 'A')
                );
        })
        ->count();
}

    private function isAllowedToBeAdded($key,$work) : bool{
        $matrixItem = $this->matrix->toArray()[$key];
        //turno mattina pomeriggio o full
        $turn = $matrixItem['turn'];
        //accetta solo lavori cash
        $only_cash = $matrixItem['only_cash_works'];
        //tipo di lavoro A, N, P, X
        $value = $work['value'];
        
        // Rispetto turno mezza giornata
        if (in_array($turn, [DayType::MORNING->value, DayType::AFTERNOON->value], true)) {
            $workTime = $this->extractWorkTime($work);

            if ($turn === DayType::MORNING->value && $workTime > '13:00') {
                return false;
            }
            if ($turn === DayType::AFTERNOON->value && $workTime < '13:31') {
                return false;
            }
        }

        if ($only_cash && $value === WorkType::AGENCY->value) {
            return false;
        }

        return true;
        //if ($key == 2 && $work['value'] == "A") {return false;}
        //return !(!empty($matrixItem['blocked_works']) && in_array($value, $matrixItem['blocked_works'], true));

    }

    private function getCapacityLeft($key,$forFixed=false) : int
    {
        $matrixItem = $this->matrix->toArray()[$key];
        
        $totalSlots = $matrixItem['slots_occupied'] ?? 0;

        // Somma tutti gli slot già occupati nella matrice
        $usedSlots = collect($matrixItem['worksMap'])
            ->filter()                     // ignora i null
            ->count();                 // somma i valori di slot

        if ($forFixed) { return $totalSlots - $usedSlots; }
        return $totalSlots - $usedSlots - $this->countFixedWorks($key);
    }

    private function addToUnassigned($work)
    {
        $this->unassignedWorks->push($work);
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
     * Distribuisce i lavori in round-robin rispettando:
     * - turno mezza giornata (morning/afternoon)
     * - capacità reale della licenza (real_slots_today)
     * - primo slot libero disponibile
     * - round-robin perfetto (ricomincia dalla prima)
     */

   public function distribute($worksToAssign,$fromFirst = false) {

        // 1. Determina il numero di slot (Colonne). Dal tuo snippet, sono 25 (indice 0 a 24).
    $MAX_SLOTS_INDEX = 24;
    // 2. Pre-carica gli elementi della Collection in un array per semplicità,
    //    sebbene Collection supporti il foreach diretto.
    // $slotIndex andrà da 0 a 24

    $matrix = $this->getMatrix();

    if ($fromFirst) {
        $startingIndex = array_search(null, $matrix[0]['worksMap'], true);
    }
    else {
        $startingIndex = 0;
    }

    for ($slotIndex = $startingIndex; $slotIndex <= $MAX_SLOTS_INDEX; $slotIndex++) {

        foreach ($this->matrix as $key => $licenseData) {

            //$licenseId = $licenseData['id'];
            $worksMap = $licenseData['worksMap'];
            // Accedi alla cella specifica (fissando lo slot e variando la licenza)
            $work = $worksMap[$slotIndex] ?? null;
           
            // Logica Round-Robin (esegue un'azione per quello slot per tutte le licenze)
            if (!is_null($work)) {
                continue;
            } else {
                // La cella è vuota in questo slot per questa licenza

                if ($this->getCapacityLeft($key)>0 && !$worksToAssign->isEmpty()) {

                    $nextWork = $worksToAssign->first();

                    if ($this->isAllowedToBeAdded($key,$nextWork)) {
                        $matrix[$key]['worksMap'][$slotIndex] = $nextWork;
                        $this->saveMatrix($matrix);
                        $worksToAssign->shift();
                    }

                    if($worksToAssign->isEmpty()) {break 2;}
                }
            }
        }
    }
    //lavori rimasti non assegnati
                foreach($worksToAssign as $work) {
                    $this->addToUnassigned($work);
                }
   }

   public function distributeFixed($worksToAssign) {

        $matrix = $this->getMatrix();

        foreach($worksToAssign as $work) {

            $index = array_search($work['license_table_id'], array_column($matrix, 'license_table_id'));

            // Cerca il primo valore 'null' e restituisce la sua chiave (l'indice)
            $slotIndex = array_search(null, $matrix[$index]['worksMap'], true);

            //controllo capacità

            if ($this->getCapacityLeft($index,true)>0) {
                //assign work
                $matrix[$index]['worksMap'][$slotIndex] = $worksToAssign->shift();
                $this->saveMatrix($matrix);
            }
            else {
                //lavoro non assegnabile - necessario intervento manuale
                $this->addToUnassigned($work);
            }
        }
    }

}
