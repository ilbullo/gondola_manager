<?php

namespace Tests\Unit\Models;

use App\Models\AgencyWork;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyWorkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_agency_work()
    {
        $agencyWork = AgencyWork::factory()->create([
            'date' => '2023-01-01',
            'voucher' => 'voucher-789',
        ]);

        $this->assertDatabaseHas('agency_works', [
            'date' => '2023-01-01 00:00:00', // Formato completo per il database
            'voucher' => 'voucher-789',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $agencyWork = AgencyWork::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $agencyWork->user);
        $this->assertEquals($user->id, $agencyWork->user->id);
    }

    /** @test */
    public function it_belongs_to_an_agency()
    {
        $agency = Agency::factory()->create();
        $agencyWork = AgencyWork::factory()->create(['agency_id' => $agency->id]);

        $this->assertInstanceOf(Agency::class, $agencyWork->agency);
        $this->assertEquals($agency->id, $agencyWork->agency->id);
    }

    /** @test */
    public function it_casts_date_to_date()
    {
        $agencyWork = AgencyWork::factory()->create(['date' => '2023-01-01']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $agencyWork->date);
        $this->assertEquals('2023-01-01', $agencyWork->date->toDateString());
    }
}