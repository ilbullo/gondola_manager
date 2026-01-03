<?php
// tests/Feature/Security/AuthorizationTest.php
namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\{Agency, User, LegalAcceptance};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

   #[Test]
    public function it_prevents_unauthorized_access_to_admin_routes()
    {
        // 1. Creiamo un utente standard con email verificata
        $user = User::factory()->create([
            'role' => UserRole::USER,
            'email_verified_at' => now(),
        ]);

        // 2. Soddisfiamo i requisiti legali (Privacy e TOS)
        LegalAcceptance::factory()->for($user)->create();
        LegalAcceptance::factory()->for($user)->tos()->create();

        // 3. Eseguiamo la richiesta
        $response = $this->actingAs($user)
            ->get(route('user-manager'));

        // Ora riceveremo 403 (autorizzazione negata dal ruolo) e non 302 (redirect legale)
        $response->assertStatus(403);
    } 


    #[Test]
    public function it_prevents_sql_injection()
    {
        $user = User::factory()->create();

        // Tenta SQL injection
        $maliciousInput = "'; DROP TABLE users; --";

        $result = User::where('email', $maliciousInput)->first();

        // Non dovrebbe eseguire il DROP TABLE
        $this->assertNull($result);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}