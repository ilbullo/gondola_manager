<?php

namespace App\Livewire\TableManager;

use Livewire\Component;
use App\Models\{LicenseTable, WorkAssignment};

class TableManager extends Component
{
    public $licenses = false;
    public $tableConfirmed = false;

    protected $listeners = ['confirmLicenses' => 'showTable', 'editLicenses' => 'hideTable','resetLicenses' => 'clearLicenses'];

    public function mount() 
    {
        $today = now();
        $this->licenses = LicenseTable::whereDate('date',$today)->exists();
    }
 
    
    public function render()
    {
        return view('livewire.table-manager.table-manager');
    }

    public function showTable() {

        $this->tableConfirmed = true;
    }

    public function hideTable() {
        $this->tableConfirmed = false;

    }

    /** TO BE OPTIMIZED WITH DEPENDENCIES 
     * If truncate licenseTable, clear also foreign_keys
     * Remember to save the invoices works splitted to the correct table.
     */

    public function clearLicenses() {
        LicenseTable::query()->delete();
        $this->hideTable();
    }


}
