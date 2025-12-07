<?php
// tests/Feature/Listeners/UpdateLastLoginAtTest.php
namespace Tests\Feature\Listeners;

use App\Listeners\UpdateLastLoginAt;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateLastLoginAtTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_last_login_at_on_login()
    {
        $user = User::factory()->create(['last_login_at' => null]);
        
        $this->assertNull($user->last_login_at);

        // Simula il login
        event(new Login('web', $user, false));

        $user->refresh();
        
        $this->assertNotNull($user->last_login_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->last_login_at);
    }

    #[Test]
    public function it_updates_last_login_at_on_subsequent_logins()
    {
        $user = User::factory()->create([
            'last_login_at' => now()->subDays(5)
        ]);
        
        $oldLoginAt = $user->last_login_at;

        // Simula un nuovo login
        sleep(1); // Assicura che il timestamp sia diverso
        event(new Login('web', $user, false));

        $user->refresh();
        
        $this->assertNotEquals($oldLoginAt, $user->last_login_at);
        $this->assertTrue($user->last_login_at->greaterThan($oldLoginAt));
    }
}
