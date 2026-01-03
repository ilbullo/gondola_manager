<?php

namespace Tests\Feature\Security;

use App\Models\{User, LegalAcceptance};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalAcceptanceTest extends TestCase
{
    use RefreshDatabase;
    use HasFactory;

    #[Test]
    public function unaccepted_legal_terms_redirect_to_acceptance_page()
    {
        $user = User::factory()->verified()->create();

        // L'utente non ha accettato nulla
        $response = $this->actingAs($user)->get(route('dashboard'));

        // Verifica che venga reindirizzato alla pagina di accettazione
        $response->assertRedirect(route('legal.terms'));
    }

    #[Test]
    public function partial_legal_acceptance_still_redirects()
    {
        $user = User::factory()->verified()->create();

        // Accetta solo la privacy, ma non i TOS
        LegalAcceptance::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('legal.terms'));
    }

    #[Test]
    public function outdated_legal_version_triggers_reacceptance()
    {
        $user = User::factory()->verified()->create();

        // Accetta versioni vecchie
        LegalAcceptance::factory()->for($user)->outdated()->create();
        LegalAcceptance::factory()->for($user)->outdated()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        // Deve reindirizzare perché le versioni sono obsolete
        $response->assertRedirect(route('legal.terms'));
    }

    #[Test]
    public function valid_acceptance_allows_access_to_app()
    {
        $user = User::factory()->verified()->create();

        // Accetta tutto correttamente (versioni attuali)
        LegalAcceptance::factory()->for($user)->create(['version' => '1.0']);
        LegalAcceptance::factory()->for($user)->tos()->create(['version' => '1.0']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }
}