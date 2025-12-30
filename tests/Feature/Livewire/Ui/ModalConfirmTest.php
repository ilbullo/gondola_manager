<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\ModalConfirm;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ModalConfirmTest extends TestCase
{
    #[Test]
    public function it_is_hidden_by_default()
    {
        Livewire::test(ModalConfirm::class)
            ->assertSet('show', false)
            ->assertDontSee('Attenzione');
    }

    #[Test]
    public function it_opens_with_custom_data()
    {
        $data = [
            'message' => 'Vuoi resettare la tabella?',
            'confirmEvent' => 'table-reset-confirmed',
            'payload' => ['id' => 1]
        ];

        Livewire::test(ModalConfirm::class)
            ->dispatch('openConfirmModal', data: $data)
            ->assertSet('show', true)
            ->assertSet('message', 'Vuoi resettare la tabella?')
            ->assertSet('confirmEvent', 'table-reset-confirmed')
            ->assertSet('payload', ['id' => 1])
            ->assertSee('Vuoi resettare la tabella?');
    }

    #[Test]
    public function it_dispatches_confirm_event_with_payload_and_closes()
    {
        Livewire::test(ModalConfirm::class)
            ->dispatch('openConfirmModal', data: [
                'confirmEvent' => 'delete-action',
                'payload' => 42
            ])
            ->call('confirm')
            ->assertDispatched('delete-action', payload: 42)
            ->assertSet('show', false)
            ->assertSet('payload', null); // Verifica il reset()
    }

    #[Test]
    public function it_dispatches_cancel_event_if_configured_and_closes()
    {
        Livewire::test(ModalConfirm::class)
            ->dispatch('openConfirmModal', data: [
                'cancelEvent' => 'action-cancelled',
                'payload' => 'some-context'
            ])
            ->call('cancel')
            ->assertDispatched('action-cancelled', payload: 'some-context')
            ->assertSet('show', false);
    }

    #[Test]
    public function it_resets_state_completely_on_close()
    {
        Livewire::test(ModalConfirm::class)
            ->dispatch('openConfirmModal', data: [
                'message' => 'Test message',
                'confirmEvent' => 'test-event'
            ])
            ->call('close')
            ->assertSet('show', false)
            ->assertSet('message', 'Sei sicuro?') // Valore di default dopo reset
            ->assertSet('confirmEvent', null);
    }
}