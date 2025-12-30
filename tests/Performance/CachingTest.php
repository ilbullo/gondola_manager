<?php 

namespace Tests\Performance;

use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Puliamo la cache prima di ogni test per evitare interferenze
        Cache::flush();
    }

    /**
     * Verifica che il sistema di "remember" funzioni.
     * La prima volta salva, la seconda restituisce il dato senza toccare il DB.
     */
    #[Test]
    public function it_caches_frequently_accessed_data()
    {
        Agency::factory()->count(3)->create();

        // Simuliamo l'accesso alla lista agenzie (come farebbe la tua Sidebar)
        $agencies1 = Cache::remember('agencies_list', 3600, function () {
            return Agency::all();
        });

        // Verifichiamo che la chiave esista ora in cache
        $this->assertTrue(Cache::has('agencies_list'));
        
        $agencies2 = Cache::get('agencies_list');
        $this->assertCount(3, $agencies2);
        $this->assertEquals($agencies1->pluck('id'), $agencies2->pluck('id'));
    }

    /**
     * Questo è il test più IMPORTANTE per te.
     * Verifica che la logica boot() del modello Agency pulisca la cache
     * quando un'agenzia viene modificata o creata.
     */
    #[Test]
    public function it_invalidates_cache_automatically_on_model_change()
    {
        // 1. Prepariamo un'agenzia e mettiamola in cache
        $agency = Agency::factory()->create(['name' => 'Hotel A']);
        Cache::put('agencies_list', Agency::all(), 3600);
        
        $this->assertTrue(Cache::has('agencies_list'));

        // 2. Modifichiamo l'agenzia. 
        // Il metodo boot() di Agency dovrebbe chiamare Cache::forget('agencies_list')
        $agency->update(['name' => 'Hotel A Updated']);

        // 3. Verifichiamo che la cache sia stata svuotata (Invalidation)
        $this->assertFalse(Cache::has('agencies_list'), 'La cache non è stata invalidata dopo l\'update del modello!');
    }

    /**
     * Test sui Tag (Opzionale).
     * Utile se vuoi svuotare "tutta la cache delle agenzie" in un colpo solo.
     */
    #[Test]
    public function it_uses_cache_tags_if_supported()
    {
        // Nota: i tag funzionano solo con driver come Redis o Memcached.
        // Se usi 'file' o 'database', Laravel salta questa logica.
        if (in_array(config('cache.default'), ['file', 'database'])) {
            $this->markTestSkipped('Il driver cache attuale non supporta i tag.');
        }

        Cache::tags(['agencies_data'])->put('list', ['agency1', 'agency2'], 3600);
        
        // Invalida l'intero gruppo
        Cache::tags(['agencies_data'])->flush();

        $this->assertNull(Cache::tags(['agencies_data'])->get('list'));
    }
}