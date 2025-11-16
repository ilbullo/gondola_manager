<?php

namespace App\Livewire\Layout;

use App\Models\Agency;
use Livewire\Component;

class Sidebar extends Component
{
    public $workType = '';
    public $label = '';
    public $voucher = '';
    public $sharedFromFirst = false;
    public $agencyName = ''; // For work type 'A' (AGENZIA)
    public $customAgencyName = ''; // For work type 'C' (CUSTOM)
    public $slotsOccupied = 1; // Default to 1 slot
    public $agencyId = null; // Store selected agency ID
    public $amount = 90; // Nuova proprietà per importo

    public $showActions = false; // tasti tabella

    protected $listeners = [
        'selectAgency' => 'selectAgency',
        'updateWorkDetails' => 'updateWorkDetails', // Nuovo listener
    ];

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
                'classes' => 'text-white bg-sky-600 hover:bg-sky-700 focus:ring-sky-300 shadow-sky-500/40',
            ],
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
                    'wire' => "redistributeWorks"
                ],
                [
                    'id' => 'undoButton',
                    'label' => 'ANNULLA RIPARTIZIONE',
                    'classes' => 'text-white bg-orange-500 hover:bg-orange-600 focus:ring-orange-300 shadow-orange-500/40',
                    'hidden' => true,
                    'wire' => "backToOriginal"
                ],
                [
                    'id' => 'updateButton',
                    'label' => 'MODIFICA TABELLA',
                    'classes' => 'text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-300 shadow-indigo-500/40',
                    'wire' => 'editTable'
                ],
                [
                    'id' => 'resetButton',
                    'label' => 'RESET TABELLA',
                    'classes' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-300 shadow-red-500/40',
                    'wire' => 'resetTable'
                ],
            ],
        ],
    ];

    public function toggleActions()
    {
        $this->showActions = !$this->showActions;
    }

    public function openWorkDetailsModal()
    {
        \Log::info('Sidebar: Emitting openWorkDetailsModal event');
        $this->dispatch('openWorkDetailsModal');
    }

    public function updateWorkDetails($details)
    {
        \Log::info('Sidebar: Updating work details', $details);
        $this->amount = $details['amount'] ?? 90;
        $this->slotsOccupied = $details['slotsOccupied'] ?? 1;
        $this->excludeSummary = $details['excluded'] ?? false;

        // Riemetti workSelected con stato completo
        $this->emitWorkSelected();
    }

    public function resetParams()
    {
        $this->workType = '';
        $this->label = '';
        $this->voucher = '';
        $this->sharedFromFirst = false;
        $this->agencyName = '';
        $this->customAgencyName = '';
        $this->slotsOccupied = 1;
        $this->agencyId = null;
        $this->dispatch('close-agency-modal');
    }

    public function mount($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function setWorkType($value)
    {
        $this->resetFields();
        // Cerca il workType corrispondente nel config
        $workType = collect($this->config['work_types'])->firstWhere('value', $value);

        if ($value === 'clear') {
            $this->resetParams();
        } elseif ($value === 'A') {
            // Dispatch event to open modal with agencies
            $agencies = Agency::orderBy('name')->get()->map(function ($agency) {
                return ['id' => $agency->id, 'name' => $agency->name];
            })->toArray();

            $this->workType = 'A';
            $this->label = 'AGENZIA';
            $this->dispatch('open-agency-modal', data: ["agencies" => $agencies]);
        } elseif ($workType) {
            // Set work type and label for other types
            $this->workType = $workType['value'];
            $this->label = $workType['label'];
            $this->agencyId = null;
            $this->emitWorkSelected();
        } else {
            // Fallback
            $this->workType = '';
            $this->label = '';
            $this->agencyId = null;
        }
    }

    public function selectAgency($agencyId)
    {
        $agency = Agency::find($agencyId);
        if ($agency) {
            $this->agencyId = $agency->id;
            $this->agencyName = $agency->name;
            $this->dispatch('close-agency-modal');
            $this->emitWorkSelected();
        }
    }

    public function updated($propertyName)
    {
        // Emit event when any relevant property changes
        if (in_array($propertyName, ['workType', 'voucher', 'sharedFromFirst', 'agencyName', 'customAgencyName', 'slotsOccupied', 'agencyId'])) {
            $this->emitWorkSelected();
        }
    }

    protected function emitWorkSelected()
    {
        $this->dispatch('workSelected', [
            'value' => $this->workType,
            'label' => $this->label,
            'voucher' => $this->voucher,
            'sharedFromFirst' => $this->sharedFromFirst,
            'excluded'  => false,
            'agencyName' => $this->workType === 'A' ? $this->agencyName : null,
            'agencyId' => $this->workType === 'A' ? $this->agencyId : null,
            'slotsOccupied' => $this->slotsOccupied,
            'amount' => $this->amount, // Nuovo campo
        ]);
    }

    public function resetFields()
    {
        $this->voucher = '';
        $this->sharedFromFirst = false;
        $this->agencyName = '';
        $this->agencyId = null;
        $this->amount = 90;
        $this->slotsOccupied = 1;
    }

    public function resetTable()
    {
        $this->dispatch('openConfirmModal', [
            'message' => 'Resettare la tabella?',
            'confirmEvent' => 'resetLicenses',
        ]);
    }

    public function editTable()
    {
        $this->dispatch('openConfirmModal', [
            'message' => 'Vuoi modificare la tabella?',
            'confirmEvent' => 'editLicenses',
        ]);
    }

    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}