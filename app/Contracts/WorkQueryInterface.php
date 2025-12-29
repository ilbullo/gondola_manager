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
    public function allWorks(Collection|array $licenseTable): Collection;

    public function sharableWorks(Collection|array $licenseTable): Collection;

    public function sharableFirstAgencyWorks(Collection|array $licenseTable): Collection;

    public function sharableFirstCashWorks(Collection|array $licenseTable): Collection;

    public function pendingNWorks(Collection|array $licenseTable): Collection;

    public function pendingCashWorks(Collection|array $licenseTable): Collection;

    public function unsharableWorks(Collection|array $licenseTable): Collection;

    public function pendingMorningAgencyWorks(Collection|array $licenseTable): Collection;

    public function pendingAfternoonAgencyWorks(Collection|array $licenseTable): Collection;

    public function pendingPWorks(Collection|array $licenseTable): Collection;

    public function prepareMatrix(Collection|array $licenseTable): Collection;
}
