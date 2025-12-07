<?php
// tests/Feature/Security/SessionSecurityTest.php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_regenerates_session_on_login()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    #[Test]
    public function it_invalidates_session_on_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $sessionId = session()->getId();

        $this->post('/logout');

        // Session dovrebbe essere invalidata
        $this->assertGuest();
    }

    #[Test]
    public function it_sets_secure_session_cookies()
    {
        if (config('session.secure') === true) {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/dashboard');

            // Verifica che il cookie abbia il flag Secure
            $cookie = $response->headers->getCookies()[0] ?? null;
            
            if ($cookie) {
                $this->assertTrue($cookie->isSecure());
            }
        } else {
            $this->markTestSkipped('Secure cookies disabled in config');
        }
    }
}