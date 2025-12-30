<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class AgencyReportService
{
    /**
     * Trasforma la matrice dei lavori in un report aggregato per agenzie.
     * * @param array $matrix Array di righe (ogni riga ha 'user' e 'worksMap')
     * @return array Lista di servizi raggruppati e ordinati
     */
    public function generate(array $matrix): array
    {
        return collect($matrix)
            // 1. Appiattiamo tutti i lavori di tutte le licenze in un'unica collezione
            ->flatMap(function ($row) {
                $licenseNumber = $row['user']['license_number'] ?? 'N/D';
                
                return collect($row['worksMap'])
                    // Filtriamo: solo lavori di tipo Agenzia (A) non nulli
                    ->filter(fn($work) => !empty($work) && ($work['value'] ?? '') === 'A')
                    // Arricchiamo ogni lavoro con il numero di licenza del conducente
                    ->map(function ($work) use ($licenseNumber) {
                        $work['_license'] = $licenseNumber;
                        return $work;
                    });
            })
            // 2. Raggruppiamo i lavori per identificare i servizi condivisi
            ->groupBy(function ($work) {
                $agency = $work['agency']['name'] ?? $work['agency'] ?? 'Sconosciuta';
                $voucher = trim($work['voucher'] ?? '');
                
                // Se c'è il voucher, raggruppiamo per Agenzia + Voucher
                if ($voucher !== '' && $voucher !== '–') {
                    return "V_{$agency}_{$voucher}";
                }
                
                // Altrimenti raggruppiamo per Agenzia + Orario (formato H:i)
                $time = Carbon::parse($work['timestamp'] ?? now())->format('H:i');
                return "T_{$agency}_{$time}";
            })
            // 3. Trasformiamo ogni gruppo in un oggetto "Servizio" sintetico
            ->map(function (Collection $group) {
                $first = $group->first();
                $timeObj = Carbon::parse($first['timestamp'] ?? now());

                return [
                    'agency_name' => $first['agency']['name'] ?? $first['agency'] ?? 'Sconosciuta',
                    'voucher'     => trim($first['voucher'] ?? '') ?: '–',
                    'time'        => $timeObj->format('H:i'),
                    'timestamp'   => $timeObj->timestamp, // Utile per l'ordinamento
                    // Estraiamo le licenze uniche che hanno partecipato a questo servizio
                    'licenses'    => $group->pluck('_license')->unique()->sort()->values()->all(),
                    'count'       => $group->count(),
                ];
            })
            // 4. Ordiniamo cronologicamente
            ->sortBy('timestamp')
            // 5. Torniamo a un array semplice per la vista
            ->values()
            ->toArray();
    }
}