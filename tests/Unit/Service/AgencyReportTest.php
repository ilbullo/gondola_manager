<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use App\Services\AgencyReportService;
use PHPUnit\Framework\Attributes\Test;

class AgencyReportTest extends TestCase
{
    private AgencyReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new AgencyReportService();
    }

    #[Test]
    public function it_groups_multiple_cars_under_the_same_voucher(): void
    {
        // Simuliamo due licenze diverse (10 e 15) che hanno lo stesso voucher d'agenzia
        $matrix = [
            [
                'user' => ['license_number' => '10'],
                'worksMap' => [
                    1 => [
                        'value' => 'A',
                        'agency' => ['name' => 'Agency Alpha'],
                        'voucher' => 'VOUCH-123',
                        'timestamp' => '2025-01-01 10:00:00'
                    ]
                ]
            ],
            [
                'user' => ['license_number' => '15'],
                'worksMap' => [
                    1 => [
                        'value' => 'A',
                        'agency' => ['name' => 'Agency Alpha'],
                        'voucher' => 'VOUCH-123',
                        'timestamp' => '2025-01-01 10:00:00'
                    ]
                ]
            ]
        ];

        $report = $this->reportService->generate($matrix);

        // Ci aspettiamo un solo servizio aggregato con 2 licenze
        $this->assertCount(1, $report);
        $this->assertEquals('Agency Alpha', $report[0]['agency_name']);
        $this->assertEquals('VOUCH-123', $report[0]['voucher']);
        $this->assertEquals(2, $report[0]['count']);
        $this->assertEquals(['10', '15'], $report[0]['licenses']);
    }

    #[Test]
    public function it_groups_by_time_when_voucher_is_missing_or_dash(): void
    {
        // Due licenze con lo stesso orario e agenzia, ma senza voucher
        $matrix = [
            [
                'user' => ['license_number' => '20'],
                'worksMap' => [
                    1 => [
                        'value' => 'A',
                        'agency' => 'Hotel Ritz',
                        'voucher' => '–',
                        'timestamp' => '2025-01-01 08:30:00'
                    ]
                ]
            ],
            [
                'user' => ['license_number' => '22'],
                'worksMap' => [
                    1 => [
                        'value' => 'A',
                        'agency' => 'Hotel Ritz',
                        'voucher' => '',
                        'timestamp' => '2025-01-01 08:30:00'
                    ]
                ]
            ]
        ];

        $report = $this->reportService->generate($matrix);

        $this->assertCount(1, $report);
        $this->assertEquals('08:30', $report[0]['time']);
        $this->assertEquals(2, $report[0]['count']);
    }

    #[Test]
    public function it_sorts_services_chronologically(): void
    {
        $matrix = [
            [
                'user' => ['license_number' => '1'],
                'worksMap' => [
                    1 => ['value' => 'A', 'agency' => 'A1', 'timestamp' => '2025-01-01 12:00:00'],
                    2 => ['value' => 'A', 'agency' => 'A1', 'timestamp' => '2025-01-01 07:00:00']
                ]
            ]
        ];

        $report = $this->reportService->generate($matrix);

        // Il servizio delle 07:00 deve apparire prima di quello delle 12:00
        $this->assertEquals('07:00', $report[0]['time']);
        $this->assertEquals('12:00', $report[1]['time']);
    }

    #[Test]
    public function it_ignores_null_works_and_non_agency_types(): void
    {
        $matrix = [
            [
                'user' => ['license_number' => '5'],
                'worksMap' => [
                    1 => null, // Slot vuoto
                    2 => ['value' => 'X', 'agency' => 'Ignore Me'], // Lavoro Cash
                    3 => ['value' => 'A', 'agency' => 'Report Me', 'timestamp' => '2025-01-01 09:00:00']
                ]
            ]
        ];

        $report = $this->reportService->generate($matrix);

        // Deve contenere solo il lavoro 'A'
        $this->assertCount(1, $report);
        $this->assertEquals('Report Me', $report[0]['agency_name']);
    }
}