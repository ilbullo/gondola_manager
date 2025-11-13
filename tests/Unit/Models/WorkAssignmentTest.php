<?php

namespace Tests\Unit\Models;

use App\Models\WorkAssignment;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
   public function it_can_create_a_work_assignment()
    {
        $user = User::factory()->create();
        $agency = Agency::factory()->create();
        $workAssignment = WorkAssignment::factory()->create([
            'user_id' => $user->id,
            'agency_id' => $agency->id,
            'slot' => 2,
            'value' => 'test-value',
            'voucher' => 'voucher-456',
            'timestamp' => now(),
            'slots_occupied' => 1,
        ]);

        $this->assertDatabaseHas('work_assignments', [
            'user_id' => $user->id,
            'agency_id' => $agency->id,
            'slot' => 2,
            'value' => 'test-value',
            'voucher' => 'voucher-456',
            'slots_occupied' => 1,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $workAssignment = WorkAssignment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $workAssignment->user);
        $this->assertEquals($user->id, $workAssignment->user->id);
    }

    /** @test */
    public function it_belongs_to_an_agency()
    {
        $agency = Agency::factory()->create();
        $workAssignment = WorkAssignment::factory()->create(['agency_id' => $agency->id]);

        $this->assertInstanceOf(Agency::class, $workAssignment->agency);
        $this->assertEquals($agency->id, $workAssignment->agency->id);
    }


    /** @test */
    public function it_casts_timestamp_to_datetime()
    {
        $workAssignment = WorkAssignment::factory()->create(['timestamp' => '2023-01-01 12:00:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $workAssignment->timestamp);
        $this->assertEquals('2023-01-01 12:00:00', $workAssignment->timestamp->toDateTimeString());
    }

    /** @test */
    public function it_casts_slot_to_integer()
    {
        $workAssignment = WorkAssignment::factory()->create(['slot' => '3']);

        $this->assertIsInt($workAssignment->slot);
        $this->assertEquals(3, $workAssignment->slot);
    }

    /** @test */
    public function it_casts_slots_occupied_to_integer()
    {
        $workAssignment = WorkAssignment::factory()->create(['slots_occupied' => '2']);

        $this->assertIsInt($workAssignment->slots_occupied);
        $this->assertEquals(2, $workAssignment->slots_occupied);
    }
}