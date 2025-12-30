<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface MatrixSplitterInterface
 *
 * @package App\Contracts
 *
 * Definisce il contratto per il motore di bilanciamento e redistribuzione lavori.
 * Il compito di un'implementazione di questa interfaccia è prendere un set di licenze
 * e i relativi lavori, applicare le regole di business (fairness, turni, capacità)
 * e restituire una matrice riorganizzata.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Interface Segregation: Riduce la dipendenza dei chiamanti a un singolo metodo
 * atomico di esecuzione ('execute').
 * 2. Dependency Inversion: Permette al sistema di iniettare diverse strategie di
 * ripartizione (es. 'StandardSplitter', 'PeakHoursSplitter') a runtime.
 * 3. Contractual Reliability: Garantisce che l'output sia sempre una Collection,
 * facilitando il concatenamento di operazioni successive (pipeline).
 *
 * FLUSSO LOGICO:
 * Input (Licenze esistenti) -> Business Logic (Regole incrociate) -> Output (Matrice Bilanciata)
 */

interface MatrixSplitterInterface
{
    /**
     * Esegue l'algoritmo di redistribuzione dei lavori sulla tabella delle licenze.
     * * Il metodo analizza lo stato attuale del $licenseTable, identifica i lavori
     * "spostabili" (sharable) e quelli in sospeso (pending), e li rialloca secondo
     * le specifiche regole di business del sistema.
     *
     * @param array|Collection $licenseTable Set di dati MatrixData (array per compatibilità Livewire o Collection).
     * * @return Collection La tabella delle licenze aggiornata con la nuova distribuzione dei lavori.
     * * @throws \App\Exceptions\MatrixSplitterException Se l'algoritmo incontra vincoli impossibili da soddisfare.
     */
    public function execute(array|Collection $licenseTable): Collection;
}