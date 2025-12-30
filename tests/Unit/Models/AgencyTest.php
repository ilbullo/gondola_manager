<?php

namespace Tests\Unit\Models;

use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AgencyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_global_scope_to_order_by_name_alphabetically()
    {
        // Creiamo agenzie in ordine sparso
        Agency::factory()->create(['name' => 'Zeta Hotel', 'code' => 'ZH']);
        Agency::factory()->create(['name' => 'Alpha Agency', 'code' => 'AA']);
        Agency::factory()->create(['name' => 'Morgana Tours', 'code' => 'MT']);

        $agencies = Agency::all();

        // Verifichiamo che la prima sia "Alpha Agency" nonostante l'ordine di inserimento
        $this->assertEquals('Alpha Agency', $agencies->first()->name);
        $this->assertEquals('Zeta Hotel', $agencies->last()->name);
    }

    #[Test]
    public function it_provides_a_display_name_accessor()
    {
        $agency = Agency::factory()->make([
            'name' => 'Hotel Plaza',
            'code' => 'HP'
        ]);

        $this->assertEquals('Hotel Plaza (HP)', $agency->display_name);
    }

    #[Test]
    public function it_can_be_found_by_code()
    {
        Agency::factory()->create(['code' => 'EX', 'name' => 'Example']);

        $found = Agency::findByCode('EX');
        $notFound = Agency::findByCode('NON-EXISTENT');

        $this->assertInstanceOf(Agency::class, $found);
        $this->assertEquals('Example', $found->name);
        $this->assertNull($notFound);
    }

    #[Test]
    public function it_invalidates_cache_on_changes()
    {
        // Prepariamo una cache finta
        Cache::put('agencies_list', ['data']);
        $this->assertTrue(Cache::has('agencies_list'));

        // Creazione -> dovrebbe svuotare la cache
        $agency = Agency::factory()->create(['name' => 'New Agency', 'code' => 'NA']);
        $this->assertFalse(Cache::has('agencies_list'), 'Cache non svuotata dopo la creazione');

        // Ripopoliamo e testiamo l'update
        Cache::put('agencies_list', ['data']);
        $agency->update(['name' => 'Updated Name']);
        $this->assertFalse(Cache::has('agencies_list'), 'Cache non svuotata dopo l\'update');

        // Ripopoliamo e testiamo il delete
        Cache::put('agencies_list', ['data']);
        $agency->delete();
        $this->assertFalse(Cache::has('agencies_list'), 'Cache non svuotata dopo il delete');
    }

    #[Test]
    public function it_supports_soft_deletes()
    {
        $agency = Agency::factory()->create();
        $agency->delete();

        // L'agenzia non dovrebbe comparire nelle query normali
        $this->assertCount(0, Agency::all());
        
        // Ma deve esistere ancora nel DB (Soft Delete)
        $this->assertDatabaseHas('agencies', ['id' => $agency->id]);
        $this->assertNotNull($agency->deleted_at);
    }

    #[Test]
    public function it_refreshes_cache_on_soft_delete_and_restore()
    {
        $agency = Agency::factory()->create(['name' => 'Test Cache']);
        
        // 1. Popoliamo la cache (simulato)
        Cache::put('agencies_list', [$agency]);

        // 2. Soft Delete
        $agency->delete();
        $this->assertFalse(Cache::has('agencies_list'), 'La cache deve essere svuotata al soft delete');

        // 3. Restore
        Cache::put('agencies_list', [$agency]);
        $agency->restore();
        $this->assertFalse(Cache::has('agencies_list'), 'La cache deve essere svuotata al restore');
    }
}