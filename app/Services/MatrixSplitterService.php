<?php

namespace App\Services;
use App\Traits\HasWorkQueries;


class MatrixSplitterService {

    use HasWorkQueries;

    public $licenseTable;
    public $matrix;

    protected array $licenses;     //l'elenco delle licenze in servizio con relativi lavori
    
    public function __construct($licenseTable) {

        $this->licenseTable = $licenseTable;
        $this->matrix = $this->prepareMatrix();
        $this->addAgencies();
    }

    protected function addAgencies() {
        $agencies = $this->worksByType("A")
                         ->where('excluded',false)
                         ->where('shared_from_first',false);
        dd($agencies);

    }



}