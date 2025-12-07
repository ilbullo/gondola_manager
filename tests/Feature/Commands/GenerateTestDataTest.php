<?php

// tests/Feature/Commands/GenerateTestDataTest.php
namespace Tests\Feature\Commands;

use App\Enums\{UserRole, WorkType};
use App\Models\{Agency, LicenseTable, User, WorkAssignment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class GenerateTestDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_users_with_correct_roles()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        // Verifica che l'admin sia stato creato
        $admin = User::where('license_number', 140)->first();
        $this->assertNotNull($admin);
        $this->assertEquals(UserRole::ADMIN, $admin->role);

        // Verifica che i bancali siano stati creati
        $bancali = User::whereIn('license_number', [143, 172, 166, 162])->get();
        $this->assertCount(4, $bancali);
        
        foreach ($bancali as $bancale) {
            $this->assertEquals(UserRole::BANCALE, $bancale->role);
        }

        // Verifica che gli utenti normali siano stati creati
        $regularUsers = User::where('role', UserRole::USER)->get();
        $this->assertGreaterThan(0, $regularUsers->count());
    }

    #[Test]
    public function it_creates_all_agencies()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        $agencies = Agency::all();
        
        // 13 agenzie nel comando
        $this->assertCount(13, $agencies);
        
        $this->assertDatabaseHas('agencies', ['code' => 'ALBA']);
        $this->assertDatabaseHas('agencies', ['code' => 'ITC']);
        $this->assertDatabaseHas('agencies', ['code' => 'GT']);
    }

    #[Test]
    public function it_creates_license_table_entries()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        $licenses = LicenseTable::whereDate('date', today())->get();
        
        // 16 workers nel comando
        $this->assertEquals(16, $licenses->count());
        
        // Verifica che abbiano un ordine
        foreach ($licenses as $license) {
            $this->assertNotNull($license->order);
            $this->assertGreaterThanOrEqual(0, $license->order);
        }
    }

    #[Test]
    public function it_creates_work_assignments()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        $works = WorkAssignment::whereDate('timestamp', today())->get();
        
        // Dovrebbe aver creato lavori per tutte le licenze
        $this->assertGreaterThan(0, $works->count());
        
        // Verifica che ci siano lavori di tipo A, X, N, P
        $this->assertTrue($works->contains('value', WorkType::AGENCY->value));
        $this->assertTrue($works->contains('value', WorkType::CASH->value));
        $this->assertTrue($works->contains('value', WorkType::NOLO->value));
        $this->assertTrue($works->contains('value', WorkType::PERDI_VOLTA->value));
    }

    #[Test]
    public function it_assigns_agency_works_correctly()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        $agencyWorks = WorkAssignment::where('value', WorkType::AGENCY->value)->get();
        
        $this->assertGreaterThan(0, $agencyWorks->count());
        
        // Ogni lavoro agenzia dovrebbe avere un'agenzia assegnata
        foreach ($agencyWorks as $work) {
            $this->assertNotNull($work->agency_id);
        }
    }

    #[Test]
    public function it_creates_morning_and_afternoon_works()
    {
        $this->artisan('app:create-table-tests')
            ->assertExitCode(0);

        // Morning works (9:00)
        $morningWorks = WorkAssignment::whereTime('timestamp', '09:00:00')->get();
        $this->assertGreaterThan(0, $morningWorks->count());

        // Afternoon works (15:30)
        $afternoonWorks = WorkAssignment::whereTime('timestamp', '15:30:00')->get();
        $this->assertGreaterThan(0, $afternoonWorks->count());
    }
}