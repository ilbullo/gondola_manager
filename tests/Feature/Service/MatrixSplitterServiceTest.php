<?php

namespace Tests\Feature\Service;

use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Worker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Enums\WorkType;

class MatrixSplitterServiceTest extends TestCase
{
    use RefreshDatabase;


    /* HELPERS */

    private function getLicenses()
    {
        return LicenseTable::with([
            'user:id,license_number',
            'works' => fn($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code'),
        ])
            ->whereDate('date', today())
            ->orderBy('order')
            ->get();
    }

    private function createMatrix()
    {
        $resource = \App\Http\Resources\LicenseResource::collection($this->getLicenses())->resolve();
        return new \App\Services\MatrixSplitterService($resource);
    }

    private function createToAssignWorks(int $howMany, array $payload = []): void
    {
        $licenseId = $payload['license_id'] ?? LicenseTable::factory()->create([
            'date' => today(), // SEMPRE oggi!
        ])->id;

        $time = $payload['time'] ?? '12:00:00';

        $attributes = [
            'license_table_id' => $licenseId,
            'timestamp'        => today()->setTimeFromTimeString($time),
        ];

        // Aggiungi campi opzionali solo se presenti
        if (isset($payload['excluded'])) {
            $attributes['excluded'] = $payload['excluded'];
        }
        if (isset($payload['value'])) {
            $attributes['value'] = $payload['value'];
        }
        if (isset($payload['amount'])) {
            $attributes['amount'] = $payload['amount'];
        }
        if (isset($payload['voucher'])) {
            $attributes['voucher'] = $payload['voucher'];
        }

        WorkAssignment::factory($howMany)->create($attributes);
    }

    #[Test]
    public function if_excluded_only_cash_and_nolo_works(): void
    {
        $fakeLicense = LicenseTable::factory()->create([
            'date'            => today(),
            'only_cash_works' => true,
        ]);

        $this->createToAssignWorks(8, ['license_id' => $fakeLicense->id]);
        $this->createToAssignWorks(5);

        $matrix = $this->createMatrix()->matrix->toArray();

        // CORRETTO: cerca per ID
        $row = collect($matrix)->firstWhere('id', $fakeLicense->id);

        $this->assertNotNull($row);
        $this->assertTrue($row['only_cash_works']); // ora passa!

        $lavoriANonEsclusi = collect($row['worksMap'])
            ->filter()
            ->filter(fn($item) => ($item['value'] ?? '') === 'A' && !$item['excluded'])
            ->count();

        $this->assertEquals(0, $lavoriANonEsclusi);
    }

    #[Test]
    public function check_cash_to_be_given_per_row(): void
    {
        $this->createToAssignWorks(10);
        $this->createToAssignWorks(8);

        $matrix = $this->createMatrix()->matrix->toArray();
        $row    = collect($matrix)->first();

        $lavoriX = collect($row['worksMap'])
            ->where('value', 'X')
            ->filter();

        $amount = $lavoriX->sum('amount');
        $countX = $lavoriX->count();

        $this->assertEquals($countX * 90, $amount);
    }

    #[Test]
    public function morning_licenses_get_only_morning_works_and_afternoon_only_afternoon(): void
    {
        // === Licenza MATTINA ===
        $morningLicense = LicenseTable::factory()->create([
            'date' => today(),
            'turn' => 'morning',
            'order' => 10,
        ]);

        // === Licenza POMERIGGIO ===
        $afternoonLicense = LicenseTable::factory()->create([
            'date' => today(),
            'turn' => 'afternoon',
            'order' => 20,
        ]);

        // === Lavori MATTINA (10:30) ===
        $this->createToAssignWorks(7, [
            'license_id' => $morningLicense->id,
            'time'       => '10:30:00'
        ]);

        // === Lavori POMERIGGIO (15:30) ===
        $this->createToAssignWorks(8, [
            'license_id' => $afternoonLicense->id,
            'time'       => '15:30:00'
        ]);

        // === Qualche lavoro "libero" che il servizio dovrà distribuire ===
        $this->createToAssignWorks(3, ['time' => '09:00:00']);  // mattina
        $this->createToAssignWorks(4, ['time' => '16:00:00']);  // pomeriggio

        // Genera la matrice
        $matrix = $this->createMatrix()->matrix->toArray();

        // --- Verifica licenza MATTINA ---
        $morningRow = collect($matrix)->firstWhere('id', $morningLicense->id);
        $this->assertNotNull($morningRow);
        $this->assertEquals('morning', $morningRow['turn']);

        $morningWorks = collect($morningRow['worksMap'])->filter();
        $afternoonWorksInMorning = $morningWorks->filter(
            fn($item) =>
            Carbon::parse($item['timestamp'])->gte(today()->setTime(13, 31))
        );

        $this->assertCount(
            0,
            $afternoonWorksInMorning,
            'Licenza morning ha ricevuto lavori pomeridiani (dopo le 13:30)!'
        );

        // --- Verifica licenza POMERIGGIO ---
        $afternoonRow = collect($matrix)->firstWhere('id', $afternoonLicense->id);
        $this->assertNotNull($afternoonRow);
        $this->assertEquals('afternoon', $afternoonRow['turn']);

        $afternoonWorks = collect($afternoonRow['worksMap'])->filter();
        $morningWorksInAfternoon = $afternoonWorks->filter(
            fn($item) =>
            Carbon::parse($item['timestamp'])->lt(today()->setTime(13, 31))
        );

        $this->assertCount(
            0,
            $morningWorksInAfternoon,
            'Licenza afternoon ha ricevuto lavori mattutini (prima delle 13:31)!'
        );
    }

    #[Test]
    public function fixed_works_stay_on_license_has_done(): void
    {
        $license = LicenseTable::factory()->create([
            'date' => today(),
        ]);

        // Lavoro FISSO (escluso = true → non deve essere spostato)
        $fixedWork = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'timestamp'        => today()->setTime(10, 0),
            'excluded'         => true,        // questo è il punto chiave
            'value'            => 'A',
        ]);

        // Altri lavori liberi da distribuire
        $this->createToAssignWorks(10, ['time' => '14:00:00']);

        $matrix = $this->createMatrix()->matrix->toArray();
        $row = collect($matrix)->firstWhere('id', $license->id);

        $this->assertNotNull($row);

        // Verifica che il lavoro fisso sia ancora lì e escluso
        $fixedInMap = collect($row['worksMap'])
            ->filter()
            ->firstWhere('id', $fixedWork->id);

        $this->assertNotNull($fixedInMap);
        $this->assertTrue($fixedInMap['excluded']);
        $this->assertEquals($license->id, $fixedInMap['license_table_id']);
    }

    #[Test]
    public function only_agency_works_can_have_shared_from_first(): void
    {
        // === LAVORO "A" → deve permettere shared_from_first = true ===
        $agencyWork = WorkAssignment::factory()->create([
            'value'             => WorkType::AGENCY->value, // 'A'
            'shared_from_first' => true,
            'timestamp'         => now(),
        ]);

        $this->assertTrue($agencyWork->shared_from_first);

        // === LAVORO "X" (cash) → NON deve permettere shared_from_first = true ===
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('shared_from_first può essere true solo per lavori di tipo \'A\'');

        WorkAssignment::factory()->create([
            'value'             => WorkType::CASH->value, // 'X'
            'shared_from_first' => true,
            'timestamp'         => now(),
        ]);
    }

}
