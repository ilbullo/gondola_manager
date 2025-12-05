<?php

namespace Tests\Unit\Models;

use App\Models\WorkAssignment;
use App\Models\LicenseTable;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WorkAssignmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_work_assignment()
    {
        $licenseTable = LicenseTable::factory()->create();
        $agency = Agency::factory()->create();
        $workAssignment = WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'agency_id' => $agency->id,
            'slot' => 2,
            'value' => 'A',
            'voucher' => 'voucher-456',
            'timestamp' => now(),
            'slots_occupied' => 1,
            'excluded' => false,
            'shared_from_first' => true,
        ]);

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'agency_id' => $agency->id,
            'slot' => 2,
            'value' => 'A',
            'voucher' => 'voucher-456',
            'slots_occupied' => 1,
            'excluded' => false,
            'shared_from_first' => true,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_license_table()
    {
        $licenseTable = LicenseTable::factory()->create();
        $workAssignment = WorkAssignment::factory()->create(['license_table_id' => $licenseTable->id]);

        $this->assertInstanceOf(LicenseTable::class, $workAssignment->licenseTable);
        $this->assertEquals($licenseTable->id, $workAssignment->licenseTable->id);
    }

    #[Test]
    public function it_belongs_to_an_agency()
    {
        $agency = Agency::factory()->create();
        $workAssignment = WorkAssignment::factory()->create(['agency_id' => $agency->id,'value' => 'A']);

        $this->assertInstanceOf(Agency::class, $workAssignment->agency);
        $this->assertEquals($agency->id, $workAssignment->agency->id);
    }

    #[Test]
    public function it_can_have_no_agency()
    {
        $licenseTable = LicenseTable::factory()->create();
        $workAssignment = WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'agency_id' => null,
        ]);

        $this->assertNull($workAssignment->agency);
    }

    #[Test]
    public function it_casts_timestamp_to_datetime()
    {
        $workAssignment = WorkAssignment::factory()->create(['timestamp' => '2023-01-01 12:00:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $workAssignment->timestamp);
        $this->assertEquals('2023-01-01 12:00:00', $workAssignment->timestamp->toDateTimeString());
    }

    #[Test]
    public function it_casts_slot_to_integer()
    {
        $workAssignment = WorkAssignment::factory()->create(['slot' => '3','value'=>'A']);

        $this->assertIsInt($workAssignment->slot);
        $this->assertEquals(3, $workAssignment->slot);
    }

    #[Test]
    public function it_casts_slots_occupied_to_integer()
    {
        $workAssignment = WorkAssignment::factory()->create(['slots_occupied' => '2']);

        $this->assertIsInt($workAssignment->slots_occupied);
        $this->assertEquals(2, $workAssignment->slots_occupied);
    }

    #[Test]
    public function it_casts_excluded_to_boolean()
    {
        $workAssignment = WorkAssignment::factory()->create(['excluded' => '1','value' => 'A']);

        $this->assertIsBool($workAssignment->excluded);
        $this->assertTrue($workAssignment->excluded);

        $workAssignment = WorkAssignment::factory()->create(['excluded' => '0']);

        $this->assertIsBool($workAssignment->excluded);
        $this->assertFalse($workAssignment->excluded);
    }

    #[Test]
    public function it_casts_shared_from_first_to_boolean()
    {
        $workAssignment = WorkAssignment::factory()->create(['shared_from_first' => '1','value' => 'A']);

        $this->assertIsBool($workAssignment->shared_from_first);
        $this->assertTrue($workAssignment->shared_from_first);

        $workAssignment = WorkAssignment::factory()->create(['shared_from_first' => '0','excluded'=>'0']);

        $this->assertIsBool($workAssignment->shared_from_first);
        $this->assertFalse($workAssignment->shared_from_first);
    }

    #[Test]
    public function it_returns_agency_name_accessor()
    {
        $agency = Agency::factory()->create(['name' => 'Test Agency']);
        $workAssignment = WorkAssignment::factory()->create(['agency_id' => $agency->id,'value' => 'A']);

        $this->assertEquals('Test Agency', $workAssignment->agency_name);

        $workAssignmentNoAgency = WorkAssignment::factory()->create(['agency_id' => null]);
        $this->assertNull($workAssignmentNoAgency->agency_name);
    }

    #[Test]
    public function it_returns_agency_code_accessor()
    {
        $agency = Agency::factory()->create(['code' => 'AG12']);
        $workAssignment = WorkAssignment::factory()->create(['agency_id' => $agency->id]);

        $this->assertEquals('AG12', $workAssignment->agency_code);

        $workAssignmentNoAgency = WorkAssignment::factory()->create(['agency_id' => null]);
        $this->assertNull($workAssignmentNoAgency->agency_code);
    }
}