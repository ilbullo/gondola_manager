<?php

// app/Traits/HasWorkQueries.php

namespace App\Traits;

use Illuminate\Support\Collection;

trait HasWorkQueries
{
    /**
     * Tutti i lavori "A" (Agenzie) nella matrice
     */
    public function agencyWorks(): Collection
    {
        return $this->worksByType('A');
    }

    /**
     * Tutti i lavori "X"
     */
    public function extraWorks(): Collection
    {
        return $this->worksByType('X');
    }

    /**
     * Tutti i lavori "P" o "N"
     */
    public function presenceWorks(): Collection
    {
        return $this->worksByType(['P', 'N']);
    }

    /**
     * Lavori esclusi (excluded = true)
     */
    public function excludedWorks(): Collection
    {
        return $this->allWorks()->where('excluded', true);
    }

    /**
     * Lavori ripartiti dal primo
     */
    public function sharedFromFirstWorks(): Collection
    {
        return $this->allWorks()->where('shared_from_first', true);
    }

    /**
     * Tutti i lavori occupati (non null)
     */
    public function allWorks(): Collection
    {
        return collect($this->licenseTable ?? [])
            ->flatMap->worksMap
            ->filter(); // rimuove automaticamente i null
    }

    /**
     * Guadagno totale (esclusi i lavori fissi)
     */
    public function totalEarnings(): float
    {
        return $this->allWorks()
            ->where('excluded', false)
            ->sum('amount');
    }

    /**
     * Guadagno totale inclusi fissi
     */
    public function totalEarningsWithFixed(): float
    {
        return $this->allWorks()->sum('amount');
    }

    /**
     * Riepilogo completo in una riga
     */
    public function workSummary(): array
    {
        return [
            'total_works'           => $this->allWorks()->count(),
            'agency_works'          => $this->agencyWorks()->count(),
            'extra_works'           => $this->extraWorks()->count(),
            'excluded_works'        => $this->excludedWorks()->count(),
            'shared_from_first'     => $this->sharedFromFirstWorks()->count(),
            'total_earnings'        => $this->totalEarnings(),
            'total_including_fixed' => $this->totalEarningsWithFixed(),
        ];
    }

    /**
     * Metodo generico privato – riutilizzato da tutti
     */
    private function worksByType(string|array $type): Collection
    {
        $types = is_array($type) ? $type : [$type];

        return $this->allWorks()->whereIn('value', $types);
    }

    /**
 * Crea una matrice vuota con lo stesso numero di righe della matrice corrente
 */
public function emptyMatrixLike(): array
{
    return collect($this->licenseTable ?? [])
        ->keys()
        ->map(fn() => [
            'id'               => null,
            'license_table_id' => null,
            'user'             => null,
            'worksMap'         => array_fill(1, 25, null),
        ])
        ->all();
}

private function prepareMatrix() {
        
        $this->matrix = $this->emptyMatrixLike(); //prepara la matrice vuota

        $columnsToCopy = array_flip(['id', 'license_table_id', 'user']); //inserisco le colonne da copiare
        
        foreach ($this->licenseTable as $rowKey => $dataRow) {
            $this->matrix[$rowKey] = array_merge(
                $this->matrix[$rowKey],
                array_intersect_key($dataRow, $columnsToCopy)
            );
        }
    }

}