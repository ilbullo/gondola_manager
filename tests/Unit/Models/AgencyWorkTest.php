<?php

namespace Tests\Unit\Models;

use App\Models\Agency;
use App\Models\AgencyWork;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AgencyWorkTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_casts_date_to_carbon_instance()
    {
        $agencyWork = AgencyWork::factory()->create([
            'date' => '2025-12-30'
        ]);

        $this->assertInstanceOf(Carbon::class, $agencyWork->date);
        $this->assertEquals(2025, $agencyWork->date->year);
        $this->assertEquals(12, $agencyWork->date->month);
    }

    #[Test]
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create(['name' => 'Mario Rossi']);
        $agencyWork = AgencyWork::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $agencyWork->user);
        $this->assertEquals('Mario Rossi', $agencyWork->user->name);
    }

    #[Test]
    public function it_belongs_to_an_agency()
    {
        $agency = Agency::factory()->create(['name' => 'Hotel Excelsior']);
        $agencyWork = AgencyWork::factory()->create(['agency_id' => $agency->id]);

        $this->assertInstanceOf(Agency::class, $agencyWork->agency);
        $this->assertEquals('Hotel Excelsior', $agencyWork->agency->name);
    }

    #[Test]
    public function it_can_handle_floating_point_amounts()
    {
        // Test per assicurarci che non ci siano arrotondamenti indesiderati a livello di Model
        $agencyWork = AgencyWork::factory()->create(['amount' => 125.55]);

        $this->assertEquals(125.55, $agencyWork->amount);
    }
}