<?php

namespace Tests\Feature\Listeners;

use App\Models\User;
use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Auth;

class UpdateLastLoginAtTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_last_login_at_timestamp_on_login_event()
    {
        // 1. Prepariamo i dati
        $user = User::factory()->create([
            'last_login_at' => null,
        ]);

        // "Congeliamo" il tempo a un secondo specifico per evitare discrepanze di millisecondi
        $knownDate = Carbon::create(2025, 12, 30, 15, 0, 0);
        Carbon::setTestNow($knownDate);

        // 2. Simuliamo l'evento
        // Creiamo l'evento di login (Laravel passa l'utente e la guard)
        $event = new Login('web', $user, false);
        
        $listener = new UpdateLastLoginAt();
        $listener->handle($event);

        // 3. Verifiche
        $user->refresh(); // Ricarichiamo i dati dal database

        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->last_login_at->eq($knownDate), "Il timestamp registrato non corrisponde a quello atteso.");
        
        // Puliamo il tempo simulato
        Carbon::setTestNow();
    }

    #[Test]
    public function it_is_actually_attached_to_the_login_event()
    {
        $user = User::factory()->create(['last_login_at' => null]);
        
        // Usiamo una data fissa senza millisecondi
        $knownDate = Carbon::now()->addDay()->startOfSecond();
        Carbon::setTestNow($knownDate);

        // Effettua il login: questo DEVE scatenare l'evento
        Auth::login($user);

        $this->assertNotNull($user->refresh()->last_login_at, "Il Listener non è stato attivato.");
        $this->assertTrue(
            $user->last_login_at->eq($knownDate), 
            "Data in DB ({$user->last_login_at}) diversa da quella attesa ({$knownDate})"
        );

        Carbon::setTestNow();
    }
}