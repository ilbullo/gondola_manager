<?php

declare(strict_types=1);

namespace App\Testing; // Namespace aggiornato alla cartella app/Testing

use Illuminate\Support\Collection;

class MatrixIntegrityTester
{
    /**
     * Simula scenari di errore per verificare la reattività del Sanity Check.
     */

    public function simulate(string $scenario, Collection $matrix): Collection
    {
        if ($matrix->isEmpty()) return $matrix;

        switch ($scenario) {
            case 'count':
                $firstKey = $matrix->keys()->first();
                $license = $matrix->get($firstKey);
                $license['slots_occupied'] = 99;
                $matrix->put($firstKey, $license);
                break;

            case 'overflow':
                $firstKey = $matrix->keys()->first();
                $license = $matrix->get($firstKey);
                $license['target_capacity'] = 1; 
                $matrix->put($firstKey, $license);
                break;

            case 'duplicate':
                $firstKey = $matrix->keys()->first();
                $license = $matrix->get($firstKey);
                $firstWork = collect($license['worksMap'])->filter()->first();
                if ($firstWork) {
                    $license['worksMap'][20] = $firstWork;
                    $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
                    $matrix->put($firstKey, $license);
                }
                break;

            case 'wrong_shift':
    return $matrix->transform(function ($row) {
        $actual = array_filter($row['worksMap']);
        if (!empty($actual)) {
            // Gestione Enum anche qui
            $turn = $row['turn'] ?? 'full';
            $turnType = ($turn instanceof \App\Enums\DayType) ? $turn->value : $turn;

            foreach ($row['worksMap'] as $key => $work) {
                if ($work) {
                    $dt = \Carbon\Carbon::parse($work['timestamp']);
                    if ($turnType === 'full') {
                        $row['worksMap'][$key]['timestamp'] = $dt->addDay()->toDateTimeString();
                    } elseif ($turnType === 'morning') {
                        $row['worksMap'][$key]['timestamp'] = $dt->setHour(16)->toDateTimeString();
                    } else {
                        $row['worksMap'][$key]['timestamp'] = $dt->setHour(9)->toDateTimeString();
                    }
                    return $row;
                }
            }
        }
        return $row;
    });
                // NOTA: Non serve il put finale qui perché transform ha già agito
                break;
        }

        return $matrix;
    }
}
