<?php 

namespace Tests\Feature\Livewire\Component;

use Tests\TestCase;
use App\Enums\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use PHPUnit\Framework\Attributes\Test;


class WorkCellComponentTest extends TestCase
{
    use InteractsWithViews;
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_correct_agency_color_from_array()
    {
        // Simuliamo il dato che arriva dalla tua Matrix (array)
        $work = [
            'value' => 'A',
            'agency_code' => 'SKY',
            'agency_colour' => 'cyan',
            'excluded' => false
        ];

        $view = $this->blade(
            '<x-work-cell :work="$work" mode="matrix" />',
            ['work' => $work]
        );

        // Verifica che la classe colore dell'agenzia sia presente
        $view->assertSee('bg-cyan-600');
        // Verifica che il codice agenzia sia renderizzato
        $view->assertSee('SKY');
        // Verifica che non ci siano classi di errore o default se il colore è presente
        $view->assertDontSee('bg-indigo-600'); 
    }

    #[Test]
    public function it_renders_the_correct_agency_color_from_object()
    {
        $work = (object) [
            'value' => 'A',
            'agency_code' => 'FLIX',
            'agency_colour' => 'rose',
            'voucher' => 'V99' // Usiamo una stringa corta < 5 caratteri
        ];

        $view = $this->blade(
            '<x-work-cell :work="$work" mode="table" />',
            ['work' => $work]
        );

        $view->assertSee('bg-rose-600');
        $view->assertSee('FLIX');
        $view->assertSee('V99'); // Ora lo troverà perché non viene troncato
    }

    #[Test]
    public function it_uses_standard_enum_color_for_non_agency_types()
    {
        // Test per il tipo "Contanti" (X)
        $work = ['value' => 'X', 'excluded' => false];
        
        // Recuperiamo il colore atteso direttamente dall'Enum per coerenza
        $expectedColorClass = WorkType::CASH->colourButtonsClass();

        $view = $this->blade(
            '<x-work-cell :work="$work" />',
            ['work' => $work]
        );

        $view->assertSee($expectedColorClass);
        $view->assertSee('X');
    }

    #[Test]
    public function it_renders_badge_if_work_is_excluded()
    {
        $work = [
            'value' => 'A',
            'agency_code' => 'TEST',
            'excluded' => true
        ];

        $view = $this->blade(
            '<x-work-cell :work="$work" />',
            ['work' => $work]
        );

        // Invece di cercare il tag x-badge, cerchiamo l'effetto del badge.
        // Se il tuo x-badge ha un title o un testo "Escluso", cerca quello.
        // Se non sai cosa cercare, usa dd($view->getContent()) per vedere l'HTML prodotto.
        $view->assertSee('Escluso'); // Sostituisci con un testo/classe reale del tuo x-badge
    }

    #[Test]
    public function it_renders_extra_slot_content_for_splitter_view()
    {
        $work = ['value' => 'A', 'agency_code' => 'AG1'];

        $view = $this->blade(
            '<x-work-cell :work="$work">
                <span class="custom-slot-test">DA: 105</span>
            </x-work-cell>',
            ['work' => $work]
        );

        // Verifica che il contenuto dello slot sia presente nell'HTML finale
        $view->assertSee('custom-slot-test');
        $view->assertSee('DA: 105');
    }

    #[Test]
    public function it_applies_correct_layout_classes_based_on_mode()
    {
        $work = ['value' => 'X'];

        // Test modo Matrix (Pillola quadrata)
        $matrixView = $this->blade('<x-work-cell :work="$work" mode="matrix" />', ['work' => $work]);
        $matrixView->assertSee('rounded-xl');
        $matrixView->assertSee('w-11');

        // Test modo Table (Cella piatta)
        $tableView = $this->blade('<x-work-cell :work="$work" mode="table" />', ['work' => $work]);
        $tableView->assertSee('rounded-sm');
        $tableView->assertSee('w-full');
    }
}