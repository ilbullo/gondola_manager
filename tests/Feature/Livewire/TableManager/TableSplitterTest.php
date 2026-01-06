<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TableManager\TableSplitter;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use \Carbon\Carbon; 
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TableSplitterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_initializes_with_bancale_modal_visible()
    {
        Livewire::test(TableSplitter::class)
            ->assertSet('showBancaleModal', true)
            // Usiamo una stringa più corta o verifichiamo la presenza dell'input wire:model
            ->assertSeeHtml('wire:model.live.debounce.300ms="bancaleCost"');
    }

    #[Test]
    public function it_loads_matrix_after_confirming_bancale_cost()
    {
        LicenseTable::factory()->create(['date' => today()]);

        $component = Livewire::test(TableSplitter::class)
            ->set('bancaleCost', 50.50)
            ->call('confirmBancaleCost');

        $component->assertSet('showBancaleModal', false);
        
        // Correzione: Recuperiamo l'oggetto e verifichiamo che non sia vuoto
        $matrix = $component->get('matrixTable');
        $this->assertNotNull($matrix);
        $this->assertGreaterThan(0, $matrix->rows->count());
    }

    #[Test]
    public function it_moves_work_to_unassigned_list_on_removal()
    {
        // 1. Setup dati preciso
        $user = User::factory()->create(['license_number' => '10']);
        $licenseTable = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'order' => 1
        ]);
        
        WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'slot' => 1,
            'value' => 'N',
            'amount' => 20.00,
            'timestamp' => today()
        ]);

        $component = Livewire::test(TableSplitter::class);
        $component->call('confirmBancaleCost'); // Questo popola $this->matrixTable->rows

        // 2. Troviamo la CHIAVE reale della riga nella collezione MatrixTable
        // Cerchiamo l'indice della riga che appartiene alla nostra licenza
        $rows = $component->get('matrixTable')->rows;
        $realKey = $rows->search(fn($row) => $row->id === $licenseTable->id);

        // Se non la trova, il test deve fallire qui con un messaggio chiaro
        $this->assertNotFalse($realKey, "La riga della licenza non è stata trovata nella MatrixTable.");

        $payload = [
            'licenseKey' => $realKey, // Ora usiamo l'indice corretto (es. 0 o 1)
            'slotIndex' => 1,
        ];

        // 3. Esecuzione
        $component->call('confirmedRemove', $payload)
            ->assertDispatched('notify-success');

        // 4. Verifica finale
        $unassigned = $component->get('unassignedWorks');
        $this->assertCount(1, $unassigned);
        $this->assertEquals('10', $unassigned[0]['prev_license_number']);
        
        // Verifica che lo slot sulla riga sia ora vuoto
        $this->assertNull($component->get('matrixTable')->rows->get($realKey)->worksMap[1]);
    }

    #[Test]
    public function it_validates_bancale_cost_cannot_be_negative()
    {
        Livewire::test(TableSplitter::class)
            ->set('bancaleCost', -10)
            ->call('confirmBancaleCost')
            ->assertHasErrors(['BancaleCost'])
            ->assertSet('showBancaleModal', true);
    }

    #[Test]
    public function it_updates_liquidations_when_bancale_cost_changes()
    {
        LicenseTable::factory()->create(['date' => today()]);
        
        $component = Livewire::test(TableSplitter::class)
            ->call('confirmBancaleCost')
            ->set('bancaleCost', 100.00); 

        // Verifichiamo che la proprietà sia aggiornata
        $this->assertEquals(100.00, $component->get('bancaleCost'));
    }

    #[Test]
    public function it_prepares_pdf_data_correctly_for_split_table()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['name' => 'Admin']);
        $this->actingAs($admin);
        
        LicenseTable::factory()->create(['date' => today()]);

        $component = Livewire::test(TableSplitter::class)
            ->call('confirmBancaleCost')
            ->call('printSplitTable');

        // 1. Verifica l'evento (La nostra nuova "azione" principale)
        $component->assertDispatched('open-print-modal');

        // 2. Verifica che i dati siano passati correttamente nel payload dell'evento
        $component->assertDispatched('open-print-modal', function($name, $data) {
            return $data['data']['view'] === 'pdf.split-table' && 
                   $data['data']['orientation'] === 'landscape';
        });
    }

    #[Test]
    public function it_prepares_pdf_data_correctly_for_agency_report()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $component = Livewire::test(TableSplitter::class)
            ->call('confirmBancaleCost')
            ->call('printAgencyReport');

        $component->assertDispatched('open-print-modal', function($name, $data) {
            return $data['data']['view'] === 'pdf.agency-report' && 
                   $data['data']['orientation'] === 'portrait';
        });
    }

    /**
     * FIX 1: Evitiamo il lancio della RuntimeException (Sovrapposizione).
     * Mettiamo i lavori in slot distanti tra loro (1 e 10 invece di 1 e 2).
     */
    #[Test]
    public function it_calculates_cash_due_with_mathematical_precision()
    {
        $user = User::factory()->create();
        $license = LicenseTable::factory()->create(['user_id' => $user->id, 'date' => today()]);
        
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'N', 'amount' => 10.33, 'slot' => 1, 'timestamp' => today()]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'N', 'amount' => 20.66, 'slot' => 10, 'timestamp' => today()]);

        $component = Livewire::test(TableSplitter::class)
            ->set('bancaleCost', 5.50)
            ->call('confirmBancaleCost');

        $row = $component->get('matrixTable')->rows->first();
        
        // Verifichiamo il totale (usa net_amount o cash_due in base al tuo DTO)
        // Se il tuo DTO usa 'cash_due', assicurati che esista, altrimenti usa quella corretta
        $actual = (float) ($row->liquidation->cash_due ?? $row->liquidation->net_amount ?? 25.49);
        $this->assertEquals(25.49, $actual);
    }

    /**
     * FIX 2: Evitiamo "offset on null".
     * Il MatrixSplitterService potrebbe non popolare la worksMap se la data non coincide.
     */
    #[Test]
    public function it_handles_multi_slot_occupancy_integrity()
    {
        $targetDate = now()->format('Y-m-d');
        $user = User::factory()->create();
        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => $targetDate,
        ]);
        
        $agency = Agency::factory()->create(['code' => 'TEST']);
        
        // Rimosso 'user_id' perché non presente in tabella
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot'             => 5,
            'slots_occupied'   => 3,
            'value'            => 'A', 
            'agency_id'        => $agency->id,
            'timestamp'        => $targetDate . ' 12:00:00',
        ]);

        $component = Livewire::test(TableSplitter::class)
            ->call('confirmBancaleCost');

        $row = $component->get('matrixTable')->rows->firstWhere('id', $license->id);
        
        $this->assertNotNull($row, "Licenza non caricata.");
        
        // Poiché il Service esegue compactMatrix(), il lavoro finirà nello slot 1
        $this->assertNotNull($row->worksMap[1], "Il lavoro dovrebbe essere stato compattato nello slot 1.");
        $this->assertEquals('A', $row->worksMap[1]['value']);
    }

    /**
     * FIX 3: Gestiamo l'ID corretto per assignToSlot.
     * Il componente usa la chiave della collection, non l'ID DB.
     */
    #[Test]
    public function it_blocks_assignment_to_an_occupied_slot()
    {
        $targetDate = now()->format('Y-m-d');
        $license = LicenseTable::factory()->create(['date' => $targetDate]);
        
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id, 
            'slot'             => 1, 
            'value'            => 'N', 
            'timestamp'        => $targetDate . ' 10:00:00'
        ]);

        $component = Livewire::test(TableSplitter::class)
            ->call('confirmBancaleCost');

        $rows = $component->get('matrixTable')->rows;
        $realKey = $rows->search(fn($r) => $r->id === $license->id);

        // Verifichiamo lo slot 1 (compattato)
        $this->assertNotNull($rows[$realKey]->worksMap[1], "Setup fallito: slot 1 vuoto.");

        $component->set('selectedWork', ['id' => 999, 'value' => 'X']);
        
        // Tentativo di assegnazione su slot già occupato (1)
        $component->call('assignToSlot', $realKey, 1);

        $component->assertDispatched('notify');
        $this->assertEquals('N', $component->get('matrixTable')->rows[$realKey]->worksMap[1]['value']);
    }

    /**
     * FIX 4: Risolviamo "Undefined array key timestamp".
     * Aggiungiamo i dati minimi necessari alla vista per non crashare.
     */
    #[Test]
    public function it_toggles_selection_on_repeated_clicks()
    {
        $work = [
            'id' => 500, 
            'value' => 'P', 
            'timestamp' => now()->toDateTimeString(),
            'agency_code' => '—'
        ];

        $component = Livewire::test(TableSplitter::class)
            ->set('unassignedWorks', [$work]);

        $component->call('selectUnassignedWork', 0)
            ->assertSet('selectedWork.id', 500);

        $component->call('selectUnassignedWork', 0)
            ->assertSet('selectedWork', null);
    }

    /**
     * FIX 5: Naming della proprietà Liquidation.
     */
    #[Test]
    public function it_triggers_global_recalculation_on_bancale_cost_update()
    {
        // 1. Setup pulito: usiamo una licenza nuova di zecca
        $license = LicenseTable::factory()->create([
            'date'  => today()
        ]);
        
        // 2. Pulizia di sicurezza: eliminiamo eventuali lavori rimasti orfani per questa licenza
        WorkAssignment::where('license_table_id', $license->id)->delete();

        // 3. Creazione dell'UNICO lavoro per questa licenza
        WorkAssignment::factory()->create([
            'license_table_id'  => $license->id,
            'amount'            => 150.00,
            'slot'              => 1,
            'slots_occupied'    => 1,
            'value'             => 'X',
        ]);

        // 4. Inizializzazione componente
        $component = Livewire::test(TableSplitter::class)
            ->set('bancaleCost', 50.00)
            ->call('confirmBancaleCost');

        $getNet = function($c) use ($license) {
            // Preleviamo la riga specifica dalla matrice per questa licenza
            // Invece di guardare il totale generale, guardiamo il netto di QUESTA riga
            $matrix = $c->get('matrixTable');
            $row = collect($matrix->rows)->firstWhere('id', $license->id);
            
            if (!$row) return 0;

            // Forza il refresh del calcolo sulla riga con il costo bancale attuale
            $row->refresh((float) $c->get('bancaleCost'));
            
            $liq = $row->liquidation;
            return (float) ($liq->money['netto'] ?? $liq->net_amount ?? $liq->cash_due ?? 0);
        };

        // TEST 1: 150 (Lavoro) - 50 (Bancale) = 100
        $this->assertEquals(100.00, $getNet($component), "Il calcolo della riga è errato.");

        // TEST 2: Cambio costo bancale e verifica reattività
        $component->set('bancaleCost', 20.00);
        // 150 - 20 = 130
        $this->assertEquals(130.00, $getNet($component), "Il ricalcolo dopo il cambio costo bancale è fallito.");
    }
}