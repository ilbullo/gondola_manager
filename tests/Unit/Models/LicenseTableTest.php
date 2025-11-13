<?php

namespace Tests\Unit\Models;

use App\Models\LicenseTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_license_table()
    {
        $user = User::factory()->create();
        $licenseTable = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date' => '2023-01-01',
        ]);

        $this->assertDatabaseHas('license_table', [
            'user_id' => $user->id,
            'date' => '2023-01-01 00:00:00', // Formato atteso nel database
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $licenseTable = LicenseTable::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $licenseTable->user);
        $this->assertEquals($user->id, $licenseTable->user->id);
    }

    /** @test */
    public function it_casts_date_to_date()
    {
        $user = User::factory()->create();
        $licenseTable = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date' => '2023-01-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $licenseTable->date);
        $this->assertEquals('2023-01-01', $licenseTable->date->toDateString());
    }
}