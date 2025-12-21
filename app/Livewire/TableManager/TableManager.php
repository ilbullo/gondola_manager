<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class TableManager extends Component
{
    /** 
     * Indica se esistono licenze registrate per la data odierna.
     * Controllato automaticamente al mount.
     */
    public bool $hasLicenses = false;

    /** 
     * Indica se la tabella delle licenze è stata confermata.
     * Quando true, la tabella non è più modificabile.
     */
    public bool $tableConfirmed = false;

    /**
     * Indica se la modalità di redistribuzione lavori è attiva.
     * Gestisce la vista TableSplitter.
     */
    public bool $isRedistributed = false;

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * All’avvio del componente controlla se esistono licenze per oggi
     * e aggiorna lo stato interno.
     */
    public function mount(): void
    {
        $this->checkTodayLicenses();
    }

    // ===================================================================
    // Helpers principali
    // ===================================================================

    /**
     * Verifica se esistono record in license_table per la data corrente.
     * Aggiorna:
     * - hasLicenses → ci sono licenze?
     * - tableConfirmed → lo stato della tabella corrisponde alla presenza licenze
     */
    private function checkTodayLicenses(): void
    {
        $this->hasLicenses = LicenseTable::whereDate('date', today())->exists();
        $this->tableConfirmed = $this->hasLicenses;
    }

    // ===================================================================
    // Eventi Livewire (v3)
    // ===================================================================

    /**
     * Conferma ufficialmente la tabella delle licenze.
     * Usato dopo il click "Conferma" in LicenseManager.
     */
    #[On('confirmLicenses')]
    public function confirmTable(): void
    {
        $this->tableConfirmed = true;
        $this->isRedistributed = false;
    }

    /**
     * Rende nuovamente modificabile la tabella licenze.
     * Usato quando l’utente clicca "Modifica".
     */
    #[On('editLicenses')]
    public function enterEditMode(): void
    {
        $this->tableConfirmed = false;
        $this->isRedistributed = false;
    }

    /**
     * Esce dalla modalità di redistribuzione lavori e torna alla tabella principale.
     */
    #[On('goToAssignmentTable')]
    public function exitRedistributionMode(): void
    {
        $this->isRedistributed = false;
        // tableConfirmed rimane invariato (true)
    }

    /**
     * Cancella tutte le licenze presenti, tipicamente alla fine della giornata.
     * L’operazione è eseguita in transazione per garantirne l’integrità.
     */
    #[On('resetLicenses')]
    public function clearLicenses(): void
    {
        DB::transaction(function () {
            // TODO: salvare le assegnazioni o fatture splittate prima della cancellazione
            // WorkAssignment::where(...)->update([...]);

            LicenseTable::query()->delete();
        });

        $this->hasLicenses = false;
        $this->tableConfirmed = false;

        // Notifica altri componenti che dipendono dallo stato della tabella
        $this->dispatch('licensesCleared');
        $this->dispatch('tableReset');
        $this->dispatch('performRefreshLicenseBoard');
        //$this->dispatch('refreshLicenseBoard');
        $this->refreshLicenseStatus();
        
    }

    // ===================================================================
    // Utility
    // ===================================================================

    /**
     * Rinfresca manualmente lo stato della tabella.
     * Utile quando la data di lavoro cambia.
     */
    public function refreshLicenseStatus(): void
    {
        $this->checkTodayLicenses();
    }

    /**
     * Attiva la modalità "redistribuzione lavori".
     * Mostra la vista TableSplitter e notifica gli altri componenti.
     */
    #[On('callRedistributeWorks')]
    public function redistributeWorks()
    {
        $this->isRedistributed = true;
        $this->dispatch('redistributeWorks');
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Rendering del componente TableManager.
     */
    public function render()
    {
        return view('livewire.table-manager.table-manager');
    }
}
