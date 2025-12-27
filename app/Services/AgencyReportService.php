<?php 
namespace App\Services;

use Carbon\Carbon;

class AgencyReportService
{
    /**
     * Trasforma la matrice dei lavori in un report aggregato per agenzie.
     */
    public function generate(array $matrix): array
    {
        $services = [];

        foreach ($matrix as $licenseRow) {
            $licenseNumber = $licenseRow['user']['license_number'] ?? 'N/D';

            foreach ($licenseRow['worksMap'] as $work) {
                // Filtriamo solo i lavori di tipo Agenzia (A)
                if (empty($work) || ($work['value'] ?? '') !== 'A') continue;

                $agencyName = $work['agency']['name'] ?? $work['agency'] ?? 'Sconosciuta';
                $voucher = trim($work['voucher'] ?? '') ?: '–';
                $timeObj = Carbon::parse($work['timestamp'] ?? now());

                // Chiave di raggruppamento: stessa agenzia e stesso voucher (o stessa ora)
                $key = ($voucher !== '–') 
                    ? $agencyName . '|V:' . $voucher 
                    : $agencyName . '|T:' . $timeObj->format('H:i');

                if (!isset($services[$key])) {
                    $services[$key] = [
                        'agency_name' => $agencyName,
                        'voucher'     => $voucher,
                        'time'        => $timeObj->format('H:i'),
                        'licenses'    => [],
                        'count'       => 0,
                    ];
                }

                $services[$key]['licenses'][] = $licenseNumber;
                $services[$key]['count']++;
            }
        }

        // Ordiniamo per orario
        uasort($services, fn($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));

        return array_values($services);
    }
}