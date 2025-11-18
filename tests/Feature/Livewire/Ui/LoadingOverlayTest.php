<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\LoadingOverlay;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class LoadingOverlayTest extends TestCase
{
    #[Test]
    public function it_starts_hidden()
    {
        Livewire::test(LoadingOverlay::class)
            ->assertSet('isLoading', false);
    }

    #[Test]
    public function it_can_be_toggled_via_event()
    {
        Livewire::test(LoadingOverlay::class)
            ->dispatch('toggleLoading', true)
            ->assertSet('isLoading', true)
            ->dispatch('toggleLoading', false)
            ->assertSet('isLoading', false);
    }

    #[Test]
    public function it_supports_legacy_events()
    {
        Livewire::test(LoadingOverlay::class)
            ->dispatch('startLoading')
            ->assertSet('isLoading', true)
            ->dispatch('stopLoading')
            ->assertSet('isLoading', false);
    }
}