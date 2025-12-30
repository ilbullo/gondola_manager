<?php

namespace Tests\Feature\Livewire\Ui;

use App\Models\WorkAssignment;
use App\Models\Agency;
use App\Livewire\Ui\WorkLiveInfoModal;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class WorkLiveInfoModalTest extends TestCase
{
    use RefreshDatabase;

    protected WorkAssignment $work;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Forza la lingua italiana per Laravel e Carbon durante il test
        app()->setLocale('it');
        \Carbon\Carbon::setLocale('it');

        $agency = Agency::factory()->create([
            'code' => 'TA', 
            'name' => 'Agenzia Test'
        ]);

        $this->work = WorkAssignment::factory()->create([
            'value' => 'X',
            'amount' => 100.00,
            'agency_id' => $agency->id,
            'voucher' => 'V123',
            'created_at' => now()->subMinutes(30)
        ]);
    }

    #[Test]
    public function it_populates_form_on_show_work_info_event()
    {
        Livewire::test(WorkLiveInfoModal::class)
            ->dispatch('showWorkInfo', workId: $this->work->id)
            ->assertSet('open', true)
            ->assertSet('amount', 100.00)
            // Il componente estrarrà il codice tramite il metodo fill() 
            // assicurati che nel componente sia: 'agency_code' => $work->agency?->code
            ->assertSet('agency_code', 'TA'); 
    }

    #[Test]
    public function it_calculates_work_data_correctly_for_the_view()
    {
        $component = Livewire::test(WorkLiveInfoModal::class)
            ->dispatch('showWorkInfo', workId: $this->work->id);

        $workData = $component->instance()->workData;

        $this->assertEquals('Agenzia Test', $workData['agency']);
        // Usiamo assertStringContainsString per essere sicuri
        $this->assertStringContainsString('30 minuti', $workData['time_elapsed']);
        $this->assertEquals(100.0, (float)$workData['amount']);
    }

    #[Test]
    public function it_updates_work_assignment_correctly()
    {
        Livewire::test(WorkLiveInfoModal::class)
            ->dispatch('showWorkInfo', workId: $this->work->id)
            ->set('amount', 150.00)
            ->set('voucher', 'UPDATED_VOUCHER')
            ->call('save')
            ->assertDispatched('work-updated')
            ->assertDispatched('refreshTableBoard');

        $this->work->refresh();
        $this->assertEquals(150.00, $this->work->amount);
        $this->assertEquals('UPDATED_VOUCHER', $this->work->voucher);
    }

    #[Test]
    public function it_triggers_confirmation_modal_for_deletion()
    {
        Livewire::test(WorkLiveInfoModal::class)
            ->dispatch('showWorkInfo', workId: $this->work->id)
            ->call('confirmDelete')
            ->assertDispatched('openConfirmModal', function($name, $params) {
                return $params[0]['confirmEvent'] === 'confirmRemoveAssignment' 
                    && $params[0]['payload']['licenseTableId'] === $this->work->id;
            })
            ->assertSet('open', false);
    }

    #[Test]
    public function it_resets_state_on_close()
    {
        Livewire::test(WorkLiveInfoModal::class)
            ->dispatch('showWorkInfo', workId: $this->work->id)
            ->call('closeModal')
            ->assertSet('open', false)
            ->assertSet('workId', null)
            ->assertSet('voucher', null);
    }
}