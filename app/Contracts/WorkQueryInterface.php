<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface WorkQueryInterface
 *
 * @package App\Contracts
 *
 * Definisce i criteri di segmentazione del carico di lavoro per la matrice.
 * Questo contratto obbliga le implementazioni a fornire metodi per estrarre
 * specifiche "fette" di lavori (Pending, Sharable, etc.) partendo dallo stato attuale.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Interface Segregation: Isola le query di business complesse in un'unica
 * interfaccia dedicata all'analisi del carico di lavoro.
 * 2. Logical Abstraction: Nasconde i dettagli del filtraggio (es. quali flag
 * determinano se un lavoro è 'Sharable') ai servizi di calcolo.
 * 3. Matrix Preparation: Fornisce lo schema iniziale ('prepareMatrix') per
 * garantire che i dati siano pronti per l'elaborazione algoritmica.
 */

interface WorkQueryInterface
{
    /**
     * Recupera la lista completa di tutti i lavori assegnati in tutte le licenze.
     *
     * @param Collection|array $licenseTable Set di dati MatrixData.
     * @return Collection Collection piatta di tutti i lavori trovati.
     */
    public function allWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori che possono essere ridistribuiti (condivisibili).
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function sharableWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori d'agenzia marcati come "Condivisi dal 1°".
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function sharableFirstAgencyWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori contanti (X) marcati come "Condivisi dal 1°".
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function sharableFirstCashWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori di tipo Nolo (N) ancora in attesa di elaborazione o assegnazione finale.
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function pendingNWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori Contanti (X) non ancora consolidati.
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function pendingCashWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori che non possono essere spostati (es. Fissi o Esclusi).
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function unsharableWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori d'agenzia (A) previsti per il turno mattutino.
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function pendingMorningAgencyWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori d'agenzia (A) previsti per il turno pomeridiano.
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function pendingAfternoonAgencyWorks(Collection|array $licenseTable): Collection;

    /**
     * Filtra i lavori di tipo "Perdi Volta" (P).
     *
     * @param Collection|array $licenseTable
     * @return Collection
     */
    public function pendingPWorks(Collection|array $licenseTable): Collection;

    /**
     * Esegue la pre-elaborazione della matrice, normalizzando i dati 
     * o preparando le strutture per la logica di ripartizione.
     *
     * @param Collection|array $licenseTable
     * @return Collection Matrice pronta per l'algoritmo di splitting.
     */
    public function prepareMatrix(Collection|array $licenseTable): Collection;
}
