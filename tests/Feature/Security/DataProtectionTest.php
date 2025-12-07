<?php
// tests/Feature/Security/DataProtectionTest.php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Enums\UserRole;

class DataProtectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_hides_sensitive_attributes_in_json()
    {
        $user = User::factory()->create([
            'password' => 'secret',
            'remember_token' => 'token123'
        ]);

        $json = $user->toJson();

        $this->assertStringNotContainsString('secret', $json);
        $this->assertStringNotContainsString('token123', $json);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->post('/api/users', [
                'name' => 'Test',
                'email' => 'invalid-email',
                'password' => 'password123'
            ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_enforces_password_minimum_length()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->post('/api/users', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => 'short'
            ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function it_prevents_user_enumeration()
    {
        // Login con email inesistente
        $response1 = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password'
        ]);

        // Login con email esistente ma password sbagliata
        User::factory()->create([
            'email' => 'exists@test.com',
            'password' => 'correct'
        ]);

        $response2 = $this->post('/login', [
            'email' => 'exists@test.com',
            'password' => 'wrong'
        ]);

        // Entrambi dovrebbero dare lo stesso messaggio generico
        $this->assertEquals(
            $response1->getSession()->get('errors')->first('email'),
            $response2->getSession()->get('errors')->first('email')
        );
    }
}