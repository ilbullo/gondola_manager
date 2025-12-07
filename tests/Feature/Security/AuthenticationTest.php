<?php
// tests/Feature/Security/AuthenticationTest.php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_hashes_passwords_correctly()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    #[Test]
    public function it_prevents_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('correct_password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'wrong_password'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    #[Test]
    public function it_allows_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('correct_password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'correct_password'
        ]);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_logs_out_user_correctly()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
    }

    #[Test]
    public function it_throttles_login_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('password')
        ]);

        // 5 tentativi falliti
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@test.com',
                'password' => 'wrong'
            ]);
        }

        // Il 6° dovrebbe essere throttled
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'wrong'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }
}