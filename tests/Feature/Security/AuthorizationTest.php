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