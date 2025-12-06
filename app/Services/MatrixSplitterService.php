<?php

namespace App\Services;

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
    public function __construct($licenseTable)
    {
        // Converte la Collection in array se necessario
        $this->licenseTable = $licenseTable instanceof \Illuminate\Support\Collection
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

        // Distribuzione lavori di agenzia mattina ancora pendenti
        $this->distribute($this->pendingMorningAgencyWorks()->values());

        // Distribuzione lavori di agenzia pomeriggio ancora pendenti
        $this->distribute($this->pendingAfternoonAgencyWorks()->values());

        // Distribuzione lavori condivisibili (sharable) che occupano il primo slot
        $this->distribute($this->sharableFirstWorks()->values(), true);

        // Distribuzione lavori in contanti
        $this->distribute($this->pendingCashWorks()); 

        // Distribuzione lavori N/P (nolo/perdivolta) fissi
        $this->distributeFixed($this->pendingNPWorks());

        // Debug: mostra i lavori non assegnati e informazioni della matrice
        // dump($this->unassignedWorks);
        // dump($this->debugInfo());
        // dump($this->allWorks());
    }

}
