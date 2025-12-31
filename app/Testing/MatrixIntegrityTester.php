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

        $firstKey = $matrix->keys()->first();
        $license = $matrix->get($firstKey);

        switch ($scenario) {
            case 'count':
                // Sballa il contatore rispetto ai lavori reali
                $license['slots_occupied'] = 99;
                break;

            case 'overflow':
                // Forza un superamento della capacità massima
                $license['target_capacity'] = 1; 
                break;

            case 'duplicate':
                // Duplica un lavoro esistente per testare l'anti-duplicazione
                $firstWork = collect($license['worksMap'])->filter()->first();
                if ($firstWork) {
                    $license['worksMap'][20] = $firstWork;
                    $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
                }
                break;
        }

        $matrix->put($firstKey, $license);
        return $matrix;
    }
}