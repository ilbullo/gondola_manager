<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Sidebar extends Component
{
    public $workType = '';
    public $label = '';
    public $voucher = '';
    public $excludeSummary = false;
    public $agencyName = ''; // For work type 'A' (AGENZIA)
    public $customAgencyName = ''; // For work type 'C' (CUSTOM)
    public $slotsOccupied = 1; // Default to 1 slot

    public $config = [
        'work_types' => [
            [
                'id' => 'quickNoloButton',
                'label' => 'NOLO (N)',
                'value' => 'N',
                'classes' => 'text-gray-900 bg-yellow-400 hover:bg-yellow-500 focus:ring-yellow-300 shadow-yellow-400/40',
            ],
            [
                'id' => 'quickContantiButton',
                'label' => 'CONTANTI (X)',
                'value' => 'X',
                'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300 shadow-emerald-500/40',
            ],
            [
                'id' => 'selectAgencyButton',
                'label' => 'AGENZIA',
                'value' => 'A',
                'classes' => 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-300 shadow-blue-500/40',
            ],
           /*[
                'id' => 'quickCustomButton',
                'label' => 'CUSTOM',
                'value' => 'C',
                'classes' => 'text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-300 shadow-purple-500/40',
            ],*/
            [
                'id' => 'quickPerdiVoltaButton',
                'label' => 'PERDI VOLTA (P)',
                'value' => 'P',
                'classes' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-300 shadow-red-500/40',
            ],
            [
                'id' => 'clearSelectionButton',
                'label' => 'ANNULLA',
                'value' => 'clear',
                'classes' => 'text-white bg-pink-600 hover:bg-pink-700 focus:ring-pink-300 shadow-pink-500/40',
            ],
        ],
        'sections' => [
            /*'custom_input' => [
                'enabled' => true,
                'label' => 'NOME AGENZIA',
                'placeholder' => 'Es: Agenzia TEST',
                'border_color' => 'border-purple-500',
                'input_border' => 'border-purple-300 focus:border-purple-600 focus:ring-purple-300',
            ],*/
            'agency_input' => [
                'enabled' => true,
                'label' => 'AGENZIA',
                'placeholder' => 'Es: Agenzia Ufficiale',
                'border_color' => 'border-blue-500',
                'input_border' => 'border-blue-300 focus:border-blue-600 focus:ring-blue-300',
            ],
            'notes' => [
                'enabled' => true,
                'label' => 'NOTE/VOUCHER',
                'placeholder' => 'Es: Voucher 1234',
                'border_color' => 'border-emerald-500',
                'input_border' => 'border-emerald-300 focus:border-emerald-600 focus:ring-emerald-300',
            ],
            'slots' => [
                'enabled' => true,
                'label' => 'CASELLE OCCUPATE',
                'options' => [
                    ['value' => 1, 'label' => '1 Casella'],
                    ['value' => 2, 'label' => '2 Caselle'],
                ],
                'border_color' => 'border-indigo-500',
                'input_border' => 'border-indigo-300 focus:border-indigo-600 focus:ring-indigo-300',
            ],
            'summary' => [
                'enabled' => true,
                'counts' => [
                    ['label' => 'Contanti (X)', 'id' => 'countX', 'value' => 0],
                    ['label' => 'Nolo (N)', 'id' => 'countN', 'value' => 0],
                    ['label' => 'Perdi Volta (P)', 'id' => 'countP', 'value' => 0],
                    ['label' => 'Agenzie', 'id' => 'countAgency', 'value' => 0],
                    ['label' => 'Ufficio', 'id' => 'countOffice', 'value' => 0],
                ],
                'grand_total' => ['enabled' => false, 'value' => '0 EUR'],
            ],
            'actions' => [
                [
                    'id' => 'redistributeButton',
                    'label' => 'RIPARTISCI',
                    'classes' => 'text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300 shadow-emerald-500/40',
                ],
                [
                    'id' => 'undoButton',
                    'label' => 'ANNULLA RIPARTIZIONE',
                    'classes' => 'text-white bg-orange-500 hover:bg-orange-600 focus:ring-orange-300 shadow-orange-500/40',
                    'hidden' => true,
                ],
                [
                    'id' => 'resetButton',
                    'label' => 'RESET TABELLA',
                    'classes' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-300 shadow-red-500/40',
                ],
            ],
        ],
    ];

    public function mount($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function setWorkType($value)
{
    $this->resetFields();
    // Cerca il workType corrispondente nel config
    $workType = collect($this->config['work_types'])->firstWhere('value', $value);

    // Se il valore è "clear", resetta tutto
    if ($value === 'clear') {
        $this->workType = '';
        $this->label = '';
    } elseif ($workType) {
        // Se trovato, imposta entrambi i campi
        $this->workType = $workType['value'];
        $this->label = $workType['label'];
    } else {
        // Se non trovato, metti valori vuoti di sicurezza
        $this->workType = '';
        $this->label = '';
    }

    $this->emitWorkSelected();
}


    public function updated($propertyName)
    {
        // Emit event when any relevant property changes
        if (in_array($propertyName, ['workType', 'voucher', 'excludeSummary', 'agencyName', 'customAgencyName', 'slotsOccupied'])) {
            $this->emitWorkSelected();
        }
    }

    protected function emitWorkSelected()
    {
        $this->dispatch('workSelected', [
            'value'             => $this->workType,
            'label'             => $this->label,
            'voucher'           => $this->voucher,
            'excludeSummary'    => $this->excludeSummary,
            'agencyName'        => $this->workType === 'A' ? $this->agencyName : null,
            'customAgencyName'  => $this->workType === 'C' ? $this->customAgencyName : null,
            'slotsOccupied'     => $this->slotsOccupied,
        ]);
    }

    public function resetFields() {
        $this->voucher = ""; 
        $this->excludeSummary = false;
    }

    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}