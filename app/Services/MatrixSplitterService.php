<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LicenseTable;
use App\Enums\WorkType;
use Illuminate\Support\Collection;
use App\Contracts\{WorkQueryInterface, MatrixSplitterInterface, MatrixEngineInterface};

class MatrixSplitterService implements MatrixSplitterInterface
{
    public Collection $matrix;
    public Collection $unassignedWorks;
    private Collection $licenseTable;

    public function __construct(
        private WorkQueryInterface $queryService,    
        private MatrixEngineInterface $engineService 
    ) {
        $this->unassignedWorks = collect();
    }

    /**
     * Esegue la logica di splitting mantenendo l'ordine originale delle chiamate.
     */
    public function execute(array|Collection $licenseTable): Collection
    {
        // Inizializzazione dati
        $this->licenseTable = collect($licenseTable);
        //tutti i lavori
        $allWorks = $this->queryService->allWorks($this->licenseTable);
        
        // 1. Preparazione della matrice base (prepareMatrix)
        $this->matrix = $this->queryService->prepareMatrix($this->licenseTable);

        // 2. Distribuzione dei lavori "fissi" di agenzia (distributeFixed + fixedAgencyWorks)
        $this->engineService->distributeFixed(
            $this->queryService->unsharableWorks($this->licenseTable)->where('value', 'A'),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 3. Distribuzione shared from first agenzia
        $this->engineService->distribute(
            $this->queryService->sharableFirstAgencyWorks($this->licenseTable)->values(),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks,
            true // useFirstSlotOnly
        );

        // 4. Distribuzione lavori agenzia mattina pendenti
        $this->engineService->distribute(
            $this->queryService->pendingMorningAgencyWorks($this->licenseTable)->values(),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 5. Distribuzione lavori agenzia pomeriggio pendenti
        $this->engineService->distribute(
            $this->queryService->pendingAfternoonAgencyWorks($this->licenseTable)->values(),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 6. Distribuzione dei lavori "fissi" cash
        $this->engineService->distributeFixed(
            $this->queryService->unsharableWorks($this->licenseTable)->where('value', 'X'),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 7. Distribuzione shared from first cash
        $this->engineService->distribute(
            $this->queryService->sharableFirstCashWorks($this->licenseTable)->values(),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks,
            true
        );

        // 8. Distribuzione lavori N (nolo) fissi
        $this->engineService->distributeFixed(
            $this->queryService->pendingNWorks($this->licenseTable),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 9. Distribuzione lavori in contanti pendenti
        $this->engineService->distribute(
            $this->queryService->pendingCashWorks($this->licenseTable),
            $this->matrix,
            $this->unassignedWorks,
            $allWorks
        );

        // 10. Aggiunta informazioni metadata NOLO su unassigned
        $this->handleUnassignedMetadata();

        // 11. TENTATIVO DI ASSEGNAZIONE SICURO
        if ($this->unassignedWorks->isNotEmpty()) {
            $worksToTry = $this->unassignedWorks->values();
            $this->engineService->distribute($worksToTry, $this->matrix, $this->unassignedWorks, $allWorks);
            
            // Aggiorna con i soli non assegnati (escludendo i P che gestiamo dopo)
            $this->unassignedWorks = $worksToTry->filter(fn ($work) => ($work['value'] ?? '') !== 'P')->values();
        }

        // 12. AGGIUNTA DEI LAVORI DI TIPO P (Forzata)
        $this->assignPWorks();

        // 13. Compattamento finale (Sposta i null alla fine)
        $this->compactMatrix();

        return $this->matrix;
    }

    /**
     * Metadata per lavori NOLO non assegnati e ordinamento finale unassigned.
     */
    private function handleUnassignedMetadata(): void
    {
        $this->unassignedWorks = $this->unassignedWorks->map(function ($work) {
            if ($work['value'] === WorkType::NOLO->value) {
                $work['unassigned'] = true;
                $work['prev_license_number'] = LicenseTable::find($work['license_table_id'])->user->license_number ?? 'N/A';
            }
            return $work;
        });

        // Ordinamento speciale: agenzie (A) alla fine della lista unassigned
        $this->unassignedWorks = $this->unassignedWorks
            ->sortBy(fn($work) => $work['value'] === 'A' ? 100 : 0)
            ->values();
    }

    /**
     * Logica specifica per i lavori P (Perdi Volta).
     */
    private function assignPWorks(): void
    {
        foreach ($this->queryService->pendingPWorks($this->licenseTable) as $pWork) {
            $licenseId = $pWork['license_table_id'];
            $licenseIndex = $this->matrix->search(fn($row) => $row['id'] == $licenseId);

            if ($licenseIndex === false) {
                $this->unassignedWorks->push($pWork);
                continue;
            }

            $license = $this->matrix[$licenseIndex];
            $slotsNeeded = $pWork['slots_occupied'] ?? 1;

            // Cerca spazio consecutivo libero
            $startSlot = $this->engineService->findConsecutiveFreeSlots($license['worksMap'], $slotsNeeded);

            // Forza in fondo se non c'è spazio
            if ($startSlot === false) {
                $startSlot = collect($license['worksMap'])->filter()->count() + 1;
            }

            for ($i = 0; $i < $slotsNeeded; $i++) {
                $license['worksMap'][$startSlot + $i] = $pWork;
            }

            $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();
            $this->matrix[$licenseIndex] = $license;
        }
    }

    /**
     * Compattamento finale per la visualizzazione (1-25).
     */
    private function compactMatrix(): void
    {
        $this->matrix = $this->matrix->map(function ($license) {
            $works = collect($license['worksMap'])->filter()->values()->all();
            $compacted = array_fill(1, 25, null);

            foreach ($works as $index => $work) {
                $compacted[$index + 1] = $work;
            }
            
            $license['worksMap'] = $compacted;
            return $license;
        });
    }
}