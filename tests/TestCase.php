<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
abstract class TestCase extends BaseTestCase
{

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set default timezone
        date_default_timezone_set('Europe/Rome');
        
        // Set config values for testing
        config([
            'app_settings.matrix.total_slots' => 25,
            'app_settings.matrix.morning_end' => '13:00',
            'app_settings.matrix.afternoon_start' => '14:00',
            'app_settings.max_users_in_table' => 20,
        ]);
    }

    /**
     * Helper to create a complete daily table with works
     */
    protected function createDailyTableWithWorks(int $licenseCount = 3, int $worksPerLicense = 5): array
    {
        $licenses = \App\Models\LicenseTable::factory()
            ->count($licenseCount)
            ->create(['date' => today()]);

        foreach ($licenses as $index => $license) {
            $license->update(['order' => $index + 1]);
            
            \App\Models\WorkAssignment::factory()
                ->count($worksPerLicense)
                ->create([
                    'license_table_id' => $license->id,
                    'timestamp' => today()->addHours($index + 8)
                ]);
        }

        return [
            'licenses' => $licenses->fresh(),
            'total_works' => $licenseCount * $worksPerLicense
        ];
    }

    /**
     * Helper to create mixed work types scenario
     */
    protected function createMixedWorkScenario(): array
    {
        $license = \App\Models\LicenseTable::factory()->create(['date' => today()]);
        $agency = \App\Models\Agency::factory()->create();

        $cash = \App\Models\WorkAssignment::factory()->cash()->create([
            'license_table_id' => $license->id,
            'slot' => 1
        ]);

        $agency = \App\Models\WorkAssignment::factory()->agency()->create([
            'license_table_id' => $license->id,
            'slot' => 2
        ]);

        $nolo = \App\Models\WorkAssignment::factory()->nolo()->create([
            'license_table_id' => $license->id,
            'slot' => 3
        ]);

        return [
            'license' => $license->fresh(),
            'works' => [
                'cash' => $cash,
                'agency' => $agency,
                'nolo' => $nolo
            ]
        ];
    }

    /**
     * Assert that a work overlaps with another work
     */
    protected function assertWorkOverlaps(
        int $licenseTableId,
        int $slot,
        int $slotsOccupied
    ): void {
        $conflict = \App\Models\WorkAssignment::where('license_table_id', $licenseTableId)
            ->where('slot', '<=', $slot + $slotsOccupied - 1)
            ->whereRaw('slot + slots_occupied - 1 >= ?', [$slot])
            ->exists();

        $this->assertTrue($conflict, "Expected work overlap was not found");
    }

    /**
     * Assert that a license has specific work types
     */
    protected function assertLicenseHasWorkTypes(
        \App\Models\LicenseTable $license,
        array $expectedTypes
    ): void {
        $actualTypes = $license->works->pluck('value')->toArray();
        
        foreach ($expectedTypes as $type) {
            $this->assertContains(
                $type,
                $actualTypes,
                "License does not have work type: {$type}"
            );
        }
    }
}



