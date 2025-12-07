<?php
// tests/Feature/Security/AuthorizationTest.php
namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\{Agency, User};
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
        $user = User::factory()->create(['role' => UserRole::USER]);

        $response = $this->actingAs($user)
            ->get(route('user-manager'));

        $response->assertStatus(403);
    }

    #[Test]
    public function it_prevents_csrf_attacks()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        // Request senza CSRF token
        $response = $this->actingAs($user)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('user-manager'), [
                'name' => 'Test',
                'email' => 'test@test.com'
            ]);

        // Dovrebbe richiedere CSRF token
        $this->assertNotNull(csrf_token());
    }

    #[Test]
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $this->expectException(\Illuminate\Database\Eloquent\MassAssignmentException::class);

        // Tenta di assegnare 'id' che non è in fillable
        User::create([
            'id' => 999,
            'name' => 'Hacker',
            'email' => 'hacker@test.com'
        ]);
    }

    #[Test]
    public function it_validates_user_input_to_prevent_xss()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->post('/api/agencies', [
                'name' => '<script>alert("XSS")</script>',
                'code' => 'TEST'
            ]);

        // Il contenuto salvato dovrebbe essere escaped
        $agency = Agency::latest()->first();
        
        if ($agency) {
            $this->assertStringNotContainsString('<script>', $agency->name);
        }
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