<?php

namespace App\Livewire\TableManager;

use App\Models\LicenseTable;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class TableManager extends Component
{
    public bool $hasLicenses = false;        // più chiaro di $licenses
    public bool $tableConfirmed = false;     // stato attuale della tabella

    /**
     * Mount: verifica se esistono licenze per oggi
     */
    public function mount(): void
    {
        $this->checkTodayLicenses();
    }

    /**
     * Controlla se esistono record nella tabella per la data corrente
     */
    private function checkTodayLicenses(): void
    {
        $this->hasLicenses = LicenseTable::whereDate('date', today())->exists();
        $this->tableConfirmed = $this->hasLicenses;
    }

    // === Eventi Livewire (Livewire v3 style) ===

    #[On('confirmLicenses')]
    public function confirmTable(): void
    {
        $this->tableConfirmed = true;
    }

    #[On('editLicenses')]
    public function enterEditMode(): void
    {
        $this->tableConfirmed = false;
    }

    #[On('resetLicenses')]
    public function clearLicenses(): void
    {
        DB::transaction(function () {
            // TODO: Aggiungere qui la logica per salvare le fatture splittate
            // WorkAssignment::where(...)->update([...]);

            LicenseTable::query()->delete();
        });

        $this->hasLicenses = false;
        $this->tableConfirmed = false;

        // Notifica gli altri componenti (es. WorkDetailsModal, ecc.)
        $this->dispatch('licensesCleared');
        $this->dispatch('tableReset');
    }

    // === Metodi di utilità (opzionali ma utili) ===

    /**
     * Forza il refresh dello stato (utile se cambi data manualmente)
     */
    public function refreshLicenseStatus(): void
    {
        $this->checkTodayLicenses();
    }

    // === Render ===
    public function render()
    {
        return view('livewire.table-manager.table-manager');
    }
}