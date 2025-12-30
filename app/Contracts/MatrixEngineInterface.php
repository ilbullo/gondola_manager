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
    /**
     * Cerca una sequenza di slot liberi contigui all'interno della worksMap.
     * * @param array $worksMap La mappa degli slot della licenza.
     * @param int $slotsNeeded Numero di slot consecutivi richiesti (es. per lavori orari).
     * @return int|false L'indice del primo slot utile trovato, o false se non c'è spazio sufficiente.
     */
    public function findConsecutiveFreeSlots(array $worksMap, int $slotsNeeded): int|false;

    /**
     * Distribuisce i lavori "Fissi" (non spostabili) nelle rispettive licenze di origine.
     * * @param Collection $worksToAssign Lista dei lavori fissi da processare.
     * @param Collection &$matrix Riferimento alla matrice delle licenze in fase di aggiornamento.
     * @param Collection &$unassigned Riferimento alla lista dei lavori che non trovano collocazione.
     * @param Collection $allWorks Lista globale di tutti i lavori per controlli di coerenza.
     * @return void
     */
    public function distributeFixed(
        Collection $worksToAssign,
        Collection &$matrix,
        Collection &$unassigned,
        Collection $allWorks
    ): void;

    /**
     * Distribuisce i lavori dinamici/condivisibili applicando le regole di business.
     * * @param Collection $worksToAssign Lavori da distribuire.
     * @param Collection &$matrix Riferimento alla matrice delle licenze (stato corrente).
     * @param Collection &$unassigned Riferimento alla lista dei lavori non assegnati.
     * @param Collection $allWorks Lista globale di riferimento.
     * @param bool $useFirstSlotOnly Se true, tenta l'allocazione partendo rigorosamente dal primo slot disponibile.
     * @return void
     */
    public function distribute(
        Collection $worksToAssign,
        Collection &$matrix,
        Collection &$unassigned,
        Collection $allWorks,
        bool $useFirstSlotOnly = false
    ): void;

    /**
     * Calcola la capacità residua di una licenza considerando i lavori già assegnati
     * e i limiti di target configurati.
     * * @param array $license Dati della licenza (MatrixData convertito in array).
     * @param Collection $allWorks Tutti i lavori assegnati per validazioni incrociate.
     * @param bool $useTargetLimit Se true, usa il targetCapacity della licenza come limite massimo.
     * @return int Numero di slot ancora disponibili.
     */
    public function getCapacityLeft(
        array $license,
        Collection $allWorks,
        bool $useTargetLimit = true
    ): int;

    /**
     * Riordina le righe della matrice in base a criteri di priorità (es. numero licenza, turni).
     * * @param Collection &$matrix Riferimento alla matrice da ordinare sul posto.
     * @return void
     */
    public function sortMatrixRows(Collection &$matrix): void;
}
