<?php

namespace Tests\Unit\Models;

use App\Models\Agency;
use App\Models\AgencyWork;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_agency()
    {
        $agency = Agency::factory()->create([
            'name' => 'Test Agency',
            'code' => 'AG001',
        ]);

        $this->assertDatabaseHas('agencies', [
            'name' => 'Test Agency',
            'code' => 'AG001',
        ]);
    }

    /** @test */
    public function it_has_many_work_assignments()
    {
        $agency = Agency::factory()->create();
        $workAssignment = WorkAssignment::factory()->create(['agency_id' => $agency->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $agency->workAssignments);
        $this->assertCount(1, $agency->workAssignments);
        $this->assertInstanceOf(WorkAssignment::class, $agency->workAssignments->first());
    }

    /** @test */
    public function it_has_many_agency_works()
    {
        $agency = Agency::factory()->create();
        $agencyWork = AgencyWork::factory()->create(['agency_id' => $agency->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $agency->agencyWorks);
        $this->assertCount(1, $agency->agencyWorks);
        $this->assertInstanceOf(AgencyWork::class, $agency->agencyWorks->first());
    }

    /** @test */
    public function it_returns_display_name_correctly()
    {
        $agency = Agency::factory()->create([
            'name' => 'Test Agency',
            'code' => 'AG001',
        ]);

        $this->assertEquals('Test Agency (AG001)', $agency->display_name);
    }

    /** @test */
    public function it_supports_soft_deletes()
    {
        $agency = Agency::factory()->create();
        $agency->delete();

        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);
        $this->assertNotNull(Agency::withTrashed()->find($agency->id));
    }

    /** @test */
public function it_fails_to_create_agency_with_duplicate_code()
{
    Agency::factory()->create(['code' => 'AG001']);

    $this->expectException(\Illuminate\Database\QueryException::class);
    Agency::factory()->create(['code' => 'AG001']);
}
}
