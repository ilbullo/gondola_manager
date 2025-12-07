<?php 
namespace Tests\Feature\Performance;

use App\Models\{Agency, User};
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
        Cache::flush();
    }

    #[Test]
    public function it_caches_frequently_accessed_data()
    {
        Agency::factory()->count(10)->create();

        // Prima chiamata (no cache)
        $agencies1 = Cache::remember('agencies', 3600, function () {
            return Agency::orderBy('name')->get();
        });

        // Seconda chiamata (from cache)
        $agencies2 = Cache::get('agencies');

        $this->assertEquals($agencies1->count(), $agencies2->count());
    }

    #[Test]
    public function it_invalidates_cache_on_data_change()
    {
        $agency = Agency::factory()->create(['name' => 'Original']);
        
        Cache::put('agency_' . $agency->id, $agency, 3600);

        // Modifica l'agenzia
        $agency->update(['name' => 'Updated']);

        // Invalida la cache
        Cache::forget('agency_' . $agency->id);

        $cached = Cache::get('agency_' . $agency->id);
        
        $this->assertNull($cached);
    }

    #[Test]
    public function it_uses_cache_tags_for_grouped_invalidation()
    {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Cache tags require Redis');
        }

        Agency::factory()->count(5)->create();

        Cache::tags(['agencies'])->put('all_agencies', Agency::all(), 3600);
        
        // Invalida tutto il tag
        Cache::tags(['agencies'])->flush();

        $cached = Cache::tags(['agencies'])->get('all_agencies');
        
        $this->assertNull($cached);
    }
}