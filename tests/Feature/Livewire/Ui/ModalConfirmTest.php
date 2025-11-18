<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\ModalConfirm;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class ModalConfirmTest extends TestCase
{
    #[Test]
    public function it_starts_closed_with_default_message()
    {
        Livewire::test(ModalConfirm::class)
            ->assertSet('show', false)
            ->assertSet('message', 'Sei sicuro?');
    }

    #[Test]
    public function it_opens_with_custom_data()
    {
        Livewire::test(ModalConfirm::class)
            ->dispatch('openConfirmModal', [
                'message' => 'Vuoi davvero eliminare Mario?',
                'confirmEvent' => 'deleteUser',
                'payload' => ['id' => 42],
            ])
            ->assertSet('show', true)
            ->assertSet('message', 'Vuoi davvero eliminare Mario?')
            ->assertSet('confirmEvent', 'deleteUser')
            ->assertSet('confirmPayload', ['id' => 42]);
    }

    #[Test]
    public function it_emits_confirm_event_and_closes()
    {
        Livewire::test(ModalConfirm::class)
            ->set('confirmEvent', 'userDeleted')
            ->set('confirmPayload', 123)
            ->set('show', true)
            ->call('confirm')
            ->assertDispatched('userDeleted', payload: 123)
            ->assertSet('show', false);
    }

    #[Test]
    public function cancel_just_closes_the_modal()
    {
        Livewire::test(ModalConfirm::class)
            ->set('show', true)
            ->set('confirmEvent', 'something')
            ->call('cancel')
            ->assertNotDispatched('something')
            ->assertSet('show', false);
    }
}