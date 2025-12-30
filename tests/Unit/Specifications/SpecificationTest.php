<?php

namespace Tests\Unit\Specifications;

use Tests\TestCase;
use App\Specifications\TurnConstraintSpecification;
use App\Specifications\CashOnlySpecification;
use App\Enums\DayType;
use App\Enums\WorkType;
use PHPUnit\Framework\Attributes\Test;

class SpecificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app_settings.matrix.morning_end' => '14:00',
            'app_settings.matrix.afternoon_start' => '14:01',
        ]);
    }

    // --- TURN CONSTRAINT SPECIFICATION TESTS ---

    #[Test]
    public function turn_constraint_prevents_afternoon_work_for_morning_shift(): void
    {
        $spec = new TurnConstraintSpecification();
        $license = ['turn' => DayType::MORNING->value];
        
        // Test confine superiore (14:00 è OK, 14:01 è NO)
        $exactEndWork = ['timestamp' => '2025-01-01 14:00:00'];
        $justAfterWork = ['timestamp' => '2025-01-01 14:01:00'];

        $this->assertTrue($spec->isSatisfiedBy($license, $exactEndWork));
        $this->assertFalse($spec->isSatisfiedBy($license, $justAfterWork));
    }

    #[Test]
    public function turn_constraint_prevents_morning_work_for_afternoon_shift(): void
    {
        $spec = new TurnConstraintSpecification();
        $license = ['turn' => DayType::AFTERNOON->value];
        
        // Test confine inferiore (14:00 è NO, 14:01 è OK)
        $morningWork = ['timestamp' => '2025-01-01 10:00:00'];
        $exactStartWork = ['timestamp' => '2025-01-01 14:01:00'];

        $this->assertFalse($spec->isSatisfiedBy($license, $morningWork));
        $this->assertTrue($spec->isSatisfiedBy($license, $exactStartWork));
    }

    #[Test]
    public function turn_constraint_handles_missing_timestamp_gracefully(): void
    {
        $spec = new TurnConstraintSpecification();
        $license = ['turn' => DayType::MORNING->value];
        
        // Se il timestamp manca, l'helper extractWorkTime restituisce '00:00'
        // '00:00' è <= '14:00', quindi per la mattina dovrebbe passare (fail-safe)
        $emptyWork = ['timestamp' => null];

        $this->assertTrue($spec->isSatisfiedBy($license, $emptyWork));
    }

    #[Test]
    public function turn_constraint_defaults_to_true_for_unknown_turns(): void
    {
        $spec = new TurnConstraintSpecification();
        $license = ['turn' => 'NON_EXISTENT_TURN'];
        $work = ['timestamp' => '2025-01-01 12:00:00'];

        // Se il turno non è codificato, la regola deve essere permissiva (LSP)
        $this->assertTrue($spec->isSatisfiedBy($license, $work));
    }

    // --- CASH ONLY SPECIFICATION TESTS ---

    #[Test]
    public function cash_only_blocks_agency_works(): void
    {
        $spec = new CashOnlySpecification();
        $license = ['only_cash_works' => true];
        
        $agencyWork = ['value' => WorkType::AGENCY->value];
        $this->assertFalse($spec->isSatisfiedBy($license, $agencyWork));
    }

    #[Test]
    public function cash_only_allows_cash_and_nolo_works(): void
    {
        $spec = new CashOnlySpecification();
        $license = ['only_cash_works' => true];
        
        $cashWork = ['value' => WorkType::CASH->value];
        $noloWork = ['value' => WorkType::NOLO->value];

        $this->assertTrue($spec->isSatisfiedBy($license, $cashWork));
        $this->assertTrue($spec->isSatisfiedBy($license, $noloWork));
    }

    #[Test]
    public function cash_only_is_ignored_if_flag_is_false(): void
    {
        $spec = new CashOnlySpecification();
        $license = ['only_cash_works' => false];
        $agencyWork = ['value' => WorkType::AGENCY->value];

        // Se la licenza può fare tutto, l'agenzia deve passare
        $this->assertTrue($spec->isSatisfiedBy($license, $agencyWork));
    }

    #[Test]
    public function cash_only_handles_missing_work_type(): void
    {
        $spec = new CashOnlySpecification();
        $license = ['only_cash_works' => true];
        $unknownWork = ['value' => null];

        // Se il tipo lavoro è ignoto, non possiamo dire che sia un'agenzia
        $this->assertTrue($spec->isSatisfiedBy($license, $unknownWork));
    }
}