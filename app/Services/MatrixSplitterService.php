<?php
declare(strict_types=1);

namespace App\Services;
use Illuminate\Support\Collection;
use App\Traits\{HasWorkQueries, MatrixDistribution};

class MatrixSplitterService
{
    // ===================================================================
    // Traits utilizzati
    // ===================================================================
    use HasWorkQueries;       // Contiene metodi per filtrare e preparare i lavori
    use MatrixDistribution;   // Contiene logica per assegnare i lavori nella matrice

    // ===================================================================
    // Proprietà della classe
    // ===================================================================
    public $licenseTable = [];      // Array o Collection di licenze
    public $matrix;                 // La matrice in cui vengono assegnati i lavori
    public $unassignedWorks;        // Collezione di lavori non ancora assegnati
    
    // ===================================================================
    // Costruttore
    // ===================================================================
    public function __construct(array|Collection $licenseTable)
    {
        // Converte la Collection in array se necessario
        $this->licenseTable = $licenseTable instanceof Collection
            ? $licenseTable->toArray()
            : $licenseTable;

        // Inizializza i lavori non assegnati come collezione
        $this->unassignedWorks = collect($this->unassignedWorks ?? []);

        // ===================================================================
        // Preparazione della matrice e distribuzione dei lavori
        // ===================================================================

        $this->prepareMatrix();  // Crea la matrice vuota basata sulle licenze

        // Distribuzione dei lavori "fissi" di agenzia (non spostabili)
        $this->distributeFixed($this->fixedAgencyWorks()->values());

        //Distribuzione del lavori shared from first di tipo agenzia 
        $this->distribute($this->sharableFirstAgencyWorks()->values(), true);

        // Distribuzione lavori di agenzia mattina ancora pendenti
        $this->distribute($this->pendingMorningAgencyWorks()->values());

        // Distribuzione lavori di agenzia pomeriggio ancora pendenti
        $this->distribute($this->pendingAfternoonAgencyWorks()->values());

         // Distribuzione dei lavori "fissi" cash (non spostabili)
        $this->distributeFixed($this->fixedCashWorks()->values());

        //Distribuzione dei lavori shared from first di tipo cash
        $this->distribute($this->sharableFirstCashWorks()->values(), true);
        
        // Distribuzione lavori condivisibili (sharable) che occupano il primo slot
        //$this->distribute($this->sharableFirstWorks()->values(), true);

        // Distribuzione lavori N (nolo) fissi
        $this->distributeFixed($this->pendingNWorks());

        // Distribuzione lavori in contanti
        $this->distribute($this->pendingCashWorks());

        // Distribuzione lavori unassigned rimanenti
        // Aggiunta di informazioni sui lavori di tipo NOLO
        $this->unassignedWorks = $this->unassignedWorks->map(function ($work) {
            if($work['value'] === \App\Enums\WorkType::NOLO->value) {
                $work['unassigned'] = true;
                $work['prev_license_number'] = \App\Models\LicenseTable::find($work['license_table_id'])->user->license_number ?? 'N/A';
            }
            return $work; 
        });

        // Ordinamento speciale: tutti i lavori di tipo 'A' (agenzia) alla fine
        $this->unassignedWorks = $this->unassignedWorks
            ->sortBy(function ($work) {
                return $work['value'] === 'A' ? 100 : 0;   // tutti gli A vanno alla fine
            })
            ->values();
        
        // === TENTATIVO DI ASSEGNAZIONE SICURO ===
        if ($this->unassignedWorks->isNotEmpty()) {

            // Passa una COPIA della collection
            $worksToTry = $this->unassignedWorks->values(); // o clone, o ->values()

            // Prova a distribuirli
            $this->distribute($worksToTry);

            // Ora $worksToTry contiene SOLO i lavori NON assegnati
            // (quelli assegnati sono stati rimossi con shift())

            // Aggiorna la proprietà con i soli non assegnati
            $this->unassignedWorks = $worksToTry->filter(fn ($work) => ($work['value'] ?? '') !== 'P')->values();
        }

        //assegno alle rispettive licenze i lavori di tipo P
        //$this->distributeFixed($this->pendingPWorks());
        
        // ======= AGGIUNTA DEI LAVORI DI TIPO P =============
        foreach ($this->pendingPWorks() as $pWork) {
            $licenseId = $pWork['license_table_id'];

            // Trova l'indice della licenza nella matrice
            $licenseIndex = $this->matrix->search(function ($row) use ($licenseId) {
                return $row['license_table_id'] == $licenseId;
            });

            if ($licenseIndex === false) {
                $this->addToUnassigned($pWork);  // Se licenza non trovata, mandalo in unassigned
                continue;
            }

            $license = $this->matrix[$licenseIndex];
            $slotsNeeded = $pWork['slots_occupied'] ?? 1;

            // Trova spazio consecutivo libero
            $startSlot = $this->findConsecutiveFreeSlots($license['worksMap'], $slotsNeeded);

            // Se non c'è spazio consecutivo, forza in fondo
            if ($startSlot === false) {
                $occupiedCount = collect($license['worksMap'])->filter()->count();
                $startSlot = $occupiedCount + 1;
            }

            // Assegna il P
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $license['worksMap'][$startSlot + $i] = $pWork;
            }

            // Aggiorna slots_occupied
            $license['slots_occupied'] = collect($license['worksMap'])->filter()->count();

            $this->matrix[$licenseIndex] = $license;
        }

        // Salva la matrice aggiornata
        $this->saveMatrix($this->matrix->all());


        // Ordinamento visivo finale – rende la matrice bellissima per l'utente
        //$this->sortMatrixRows();
    }

}
