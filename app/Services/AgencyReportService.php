<?php
namespace App\Services;

use Carbon\Carbon;

/**
 * Class AgencyReportService
 *
 * @package App\Services
 *
 * Servizio di aggregazione e trasformazione per la reportistica agenzie.
 * Converte la matrice operativa delle licenze in una lista cronologica di servizi
 * raggruppati, ideale per la generazione di PDF o estratti conto.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Transformation: Converte una struttura orientata alle licenze (Row-based)
 * in una struttura orientata ai servizi (Service-based).
 * 2. Smart Grouping: Implementa una logica di raggruppamento basata su Voucher o Timestamp
 * per identificare servizi condivisi tra più conducenti.
 * 3. Sorting Logic: Garantisce l'ordine cronologico dei servizi per una lettura
 * professionale del documento finale.
 *
 * LOGICA DI RAGGRUPPAMENTO:
 * - Se è presente un Voucher: raggruppa tutti i lavori con lo stesso codice e agenzia.
 * - Se il Voucher è assente: utilizza l'orario (timestamp) come discriminante.
 */

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
