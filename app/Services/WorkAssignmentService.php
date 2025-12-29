<?php

namespace App\Services;

use App\Models\{WorkAssignment, Agency, LicenseTable};
use App\Http\Resources\LicenseResource;
use Illuminate\Support\Facades\Config;
use App\Enums\DayType;

/**
 * Class WorkAssignmentService
 *
 * @package App\Services
 *
 * Gestore delle operazioni CRUD e dello stato operativo delle licenze.
 * Coordina la scrittura sicura dei lavori sul database, garantendo l'assenza
 * di collisioni temporali e la corretta rotazione dei turni.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Transactional Integrity: Protegge il database da sovrapposizioni di slot
 * tramite controlli preventivi in fase di salvataggio.
 * 2. State Management: Gestisce il ciclo di vita operativo della licenza (turni e flag cash-only).
 * 3. Data Transformation: Prepara i dati per la visualizzazione tramite Resource
 * o per la generazione di documenti (PDF).
 * 4. Relationship Resolution: Risolve dinamicamente le dipendenze tra i lavori
 * e le anagrafiche agenzia.
 *
 * LOGICA DI CONFLITTO:
 * - Un lavoro non può essere salvato se lo spazio [slot -> slot + occupazione]
 * interseca un record già esistente per la stessa licenza nella data odierna.
 */

class WorkAssignmentService
{
    /**
     * Esegue il salvataggio fisico dell'assegnazione con controllo conflitti.
     */
    public function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied, array $selectedWork): void
    {
        // 1. Controllo sovrapposizioni slot
        $conflict = WorkAssignment::where('license_table_id', $licenseTableId)
            ->whereDate('timestamp', today())
            ->where(function ($q) use ($slot, $slotsOccupied) {
                $q->where('slot', '<=', $slot + $slotsOccupied - 1)
                    ->whereRaw('slot + slots_occupied - 1 >= ?', [$slot]);
            })
            ->exists();

        if ($conflict) {
            throw new \Exception('Lo slot è già occupato o si sovrappone.');
        }

        // 2. Associazione agenzia
        $agencyId = null;
        if ($selectedWork['value'] === 'A' && !empty($selectedWork['agencyName'])) {
            $agency = Agency::where('name', $selectedWork['agencyName'])->first();
            $agencyId = $agency?->id;
        }

        // 3. Creazione record
        WorkAssignment::create([
            'license_table_id'  => $licenseTableId,
            'agency_id'         => $agencyId,
            'slot'              => $slot,
            'value'             => $selectedWork['value'],
            'amount'            => $selectedWork['amount'] ?? Config::get('app_settings.works.default_amount'),
            'voucher'           => $selectedWork['voucher'] ?? null,
            'slots_occupied'    => $slotsOccupied,
            'excluded'          => $selectedWork['excluded'] ?? false,
            'shared_from_first' => $selectedWork['sharedFromFirst'] ?? false,
            'timestamp'         => now(),
        ]);
    }

    /**
     * Rimuove un'assegnazione specifica.
     */
    public function deleteAssignment(int $id): bool
    {
        $assignment = WorkAssignment::find($id);

        if (!$assignment) {
            throw new \Exception("Lavoro non trovato o già rimosso.");
        }

        return $assignment->delete();
    }

    /**
     * Recupera i dati e li trasforma tramite la Resource.
     */
    public function refreshTable(): array
    {
        $licenses = LicenseTable::with([
            'user:id,name,license_number',
            'works' => fn($q) => $q->whereDate('timestamp', today())
                ->orderBy('slot')
                ->with('agency:id,name,code'),
        ])
        ->whereDate('date', today())
        ->orderBy('order')
        ->get();

        return LicenseResource::collection($licenses)->resolve();
    }

    /**
     * Ruota il turno della licenza secondo la sequenza definita.
     */
    public function cycleLicenseTurn(int $licenseId): string
    {
        $license = LicenseTable::findOrFail($licenseId);

        // Usiamo direttamente i casi dell'Enum
        $nextTurn = match($license->turn) {
            DayType::FULL      => DayType::MORNING,
            DayType::MORNING   => DayType::AFTERNOON,
            DayType::AFTERNOON => DayType::FULL,
            default            => DayType::FULL,
        };

        $license->update(['turn' => $nextTurn]);
    }

    /**
     * Inverte lo stato del flag only_cash_works.
     */
    public function toggleLicenseCashOnly(int $licenseId): bool
    {
        $license = \App\Models\LicenseTable::findOrFail($licenseId);
        $license->only_cash_works = !$license->only_cash_works;
        $license->save();

        return $license->only_cash_works;
    }

    /**
     * Prepara i dati della matrice per la stampa PDF.
     */
    public function preparePdfData(array $licenses): array
    {
        return collect($licenses)->map(function ($license) {
            return [
                'license_number' => $license['user']['license_number'] ?? '—',
                'worksMap'       => $license['worksMap'],
            ];
        })->sortBy('license_number')->values()->toArray();
    }
}
