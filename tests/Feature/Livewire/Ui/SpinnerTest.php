<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\Spinner;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SpinnerTest extends TestCase
{
    #[Test]
    public function it_renders_with_default_text()
    {
        Livewire::test(Spinner::class)
            ->assertSee('Sincronizzazione...');
    }

    #[Test]
    public function it_renders_with_custom_text()
    {
        Livewire::test(Spinner::class, ['text' => 'Caricamento'])
            ->assertSee('Caricamento...');
    }

    #[Test]
    public function it_has_correct_visual_classes()
    {
        // Verifichiamo che le classi CSS critiche per l'overlay siano presenti
        Livewire::test(Spinner::class)
            ->assertSee('fixed inset-0')
            ->assertSee('animate-spin');
    }
}