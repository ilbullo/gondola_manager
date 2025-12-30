<?php

namespace App\Services;

use App\Models\{WorkAssignment, Agency, LicenseTable};
use App\Http\Resources\LicenseResource;
use Illuminate\Support\Facades\Config;
use App\Enums\DayType;
use Illuminate\Support\Collection;

class WorkAssignmentService
{
    /**
     * Esegue il salvataggio fisico dell'assegnazione con controllo conflitti.
     */
    public function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied, array $selectedWork): void
    {
        // 1. Controllo sovrapposizioni slot (Logic di protezione dello spazio temporale)
        $conflict = WorkAssignment::where('license_table_id', $licenseTableId)
            ->whereDate('timestamp', today())
            ->where(function ($q) use ($slot, $slotsOccupied) {
                $q->where('slot', '<=', $slot + $slotsOccupied - 1)
                  ->whereRaw('slot + slots_occupied - 1 >= ?', [$slot]);
            })
            ->exists();

        if ($conflict) {
            throw new \Exception('Lo slot è già occupato o si sovrappone a un lavoro esistente.');
        }

        // 2. Risoluzione Agenzia
        $agencyId = null;
        if (($selectedWork['value'] ?? '') === 'A' && !empty($selectedWork['agencyName'])) {
            $agency = Agency::where('name', $selectedWork['agencyName'])->first();
            $agencyId = $agency?->id;
        }

        // 3. Persistenza
        WorkAssignment::create([
            'license_table_id'  => $licenseTableId,
            'agency_id'         => $agencyId,
            'slot'              => $slot,
            'value'             => $selectedWork['value'],
            'amount'            => $selectedWork['amount'] ?? Config::get('app_settings.works.default_amount', 90.0),
            'voucher'           => $selectedWork['voucher'] ?? null,
            'slots_occupied'    => $slotsOccupied,
            'excluded'          => $selectedWork['excluded'] ?? false,
            'shared_from_first' => $selectedWork['sharedFromFirst'] ?? false,
            'timestamp'         => now(),
        ]);
    }

    /**
     * Recupera i dati freschi per la tabella Livewire.
     */
    public function refreshTable(): array
    {
        return LicenseResource::collection($this->getBaseQuery()->get())->resolve();
    }

    /**
     * Prepara i dati per la stampa PDF garantendo coerenza con la vista a schermo.
     */
    public function preparePdfData(array $licenses): array
    {
        // Usiamo le Collection per garantire che i dati siano puliti per il PDF
        return collect($licenses)->map(function ($license) {
            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'name'           => $license['user']['name'] ?? '—',
                'worksMap'       => $license['worksMap'], // Assicurati che il Resource riempia i 25 slot
            ];
        })
        // Non usiamo sortBy('license_number') se vogliamo mantenere l'ordine operativo (turno/bancale)
        ->values() 
        ->toArray();
    }

    /**
     * Metodi di gestione stato licenza (Turno e Cash Only)
     */
    public function cycleLicenseTurn(int $licenseId): void
    {
        $license = LicenseTable::findOrFail($licenseId);
        
        // Usiamo ->value se stiamo confrontando stringhe o l'oggetto se c'è il casting
        $currentTurn = $license->turn instanceof DayType ? $license->turn : DayType::from($license->turn);

        $newTurn = match($currentTurn) {
            DayType::FULL      => DayType::MORNING,
            DayType::MORNING   => DayType::AFTERNOON,
            DayType::AFTERNOON => DayType::FULL,
            default            => DayType::FULL,
        };

        $license->update(['turn' => $newTurn]);
    }

    public function toggleLicenseCashOnly(int $licenseId): bool
    {
        $license = LicenseTable::findOrFail($licenseId);
        $license->only_cash_works = !$license->only_cash_works;
        $license->save();

        return $license->only_cash_works;
    }

    /**
     * Query di base centralizzata per SRP (Single Responsibility Principle)
     */
    private function getBaseQuery()
    {
        return LicenseTable::with([
            'user:id,name,license_number',
            'works' => fn($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code'),
        ])
        ->whereDate('date', today())
        ->orderBy('order'); // Mantiene l'ordine stabilito dal "Bancale"
    }

    public function deleteAssignment(int $id): bool
    {
        $assignment = WorkAssignment::findOrFail($id);
        return $assignment->delete();
    }

    /**
     * Calcola il totale degli incassi per una licenza specifica.
     * * @param int $licenseTableId
     * @return float
     */
    public function getLicenseTotal(int $licenseTableId): float
    {
        return (float) \App\Models\WorkAssignment::where('license_table_id', $licenseTableId)
            ->sum('amount');
    }
 
}