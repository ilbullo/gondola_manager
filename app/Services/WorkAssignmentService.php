<?php

namespace App\Services;

use App\Models\{WorkAssignment, Agency, LicenseTable};
use App\Http\Resources\LicenseResource;
use Illuminate\Support\Facades\Config;
use App\Enums\DayType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\DataObjects\LiquidationResult;

class WorkAssignmentService
{
    /**
     * Esegue il salvataggio fisico dell'assegnazione con controllo conflitti e lock atomico.
     */
    public function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied, array $selectedWork): void
    {
        $lockKey = "save-assignment-license-{$licenseTableId}-" . today()->format('Y-m-d');
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            throw new \Exception('Un altro utente sta aggiornando questa licenza. Riprova tra pochi istanti.');
        }

        try {
            // 1. Validazione estratti (Integrità spaziale)
            $this->ensureNoSlotOverlap($licenseTableId, $slot, $slotsOccupied);

            // 2. Risoluzione Agenzia
            $agencyId = $this->resolveAgencyId($selectedWork);

            // 3. Persistenza (avvolta in transazione per sicurezza extra)
            \Illuminate\Support\Facades\DB::transaction(function () use ($licenseTableId, $slot, $slotsOccupied, $selectedWork, $agencyId) {
                WorkAssignment::create([
                    'license_table_id'  => $licenseTableId,
                    'agency_id'         => $agencyId,
                    'slot'              => $slot,
                    'value'             => $selectedWork['value'],
                    'amount'            => $selectedWork['amount'] ?? config('app_settings.works.default_amount', 90.0),
                    'voucher'           => $selectedWork['voucher'] ?? null,
                    'slots_occupied'    => $slotsOccupied,
                    'excluded'          => $selectedWork['excluded'] ?? false,
                    'shared_from_first' => $selectedWork['sharedFromFirst'] ?? false,
                    'timestamp'         => now(),
                ]);
            });

        } finally {
            $lock->release();
        }
    }

    /**
     * Verifica che il range di slot richiesto non si sovrapponga a lavori esistenti.
     * * @throws \Exception
     */
    private function ensureNoSlotOverlap(int $licenseTableId, int $slot, int $slotsOccupied): void
    {
        // Calcoliamo i confini del nuovo lavoro
        $start = $slot;
        $end = $slot + $slotsOccupied - 1;

        $conflict = WorkAssignment::where('license_table_id', $licenseTableId)
            ->whereDate('timestamp', today())
            ->where(function ($q) use ($start, $end) {
                /**
                 * Logica di sovrapposizione:
                 * Un lavoro esistente (E_start, E_end) confligge con il nuovo (N_start, N_end) se:
                 * E_start <= N_end AND E_end >= N_start
                 */
                $q->where('slot', '<=', $end)
                ->whereRaw('slot + slots_occupied - 1 >= ?', [$start]);
            })
            ->exists();

        if ($conflict) {
            throw new \Exception("Lo slot richiesto ($start-$end) è già occupato o si sovrappone a un lavoro esistente.");
        }
    }

    /**
     * Risolve l'ID dell'agenzia partendo dal nome.
     */
    private function resolveAgencyId(array $selectedWork): ?int
    {
        if (($selectedWork['value'] ?? '') === 'A' && !empty($selectedWork['agencyName'])) {
            return Agency::where('name', $selectedWork['agencyName'])->value('id');
        }
        return null;
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
        $lockKey = "cycle-turn-license-{$licenseId}";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            throw new \Exception('Aggiornamento turno in corso...');
        }

        try {
            $license = LicenseTable::findOrFail($licenseId);

            $currentTurn = $license->turn instanceof DayType ? $license->turn : DayType::from($license->turn);

            $newTurn = match($currentTurn) {
                DayType::FULL      => DayType::MORNING,
                DayType::MORNING   => DayType::AFTERNOON,
                DayType::AFTERNOON => DayType::FULL,
                default            => DayType::FULL,
            };

            $license->update(['turn' => $newTurn]);
        } finally {
            $lock->release();
        }
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
        // Definiamo la chiave di lock basata sull'ID specifico del lavoro
        $lockKey = "action-assignment-{$id}";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            throw new \Exception('Impossibile eliminare: operazione in corso su questo lavoro.');
        }

        try {
            $assignment = WorkAssignment::findOrFail($id);
            return $assignment->delete();
        } finally {
            // Rilasciamo il lock solo se lo abbiamo acquisito con successo
            $lock->release();
        }
    }

    /**
     * Calcola il totale degli incassi per una licenza specifica.
     * * @param int $licenseTableId
     * @return float
     */
    public function getLicenseTotal(int $licenseTableId): float
    {
        return (float) WorkAssignment::where('license_table_id', $licenseTableId)
            ->sum('amount');
    }

    /**
     * Genera i parametri standard per i report della tabella assegnazione.
     */
    public function getAssignmentReportParams(iterable $licenses): array
    {
        return [
            'view'        => 'pdf.work-assignment-table',
            'data'        => [
                'matrix'      => $this->preparePdfData($licenses),
                'generatedBy' => Auth::user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'date'        => today()->format('d/m/Y')
            ],
            'filename'    => 'tabella_assegnazione_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'landscape',
            'paper'       => 'a4', // O 'a4' a seconda delle necessità
        ];
    }

    /**
     * Genera i parametri completi per la Tabella Ripartizione (Split Table).
     */
    public function getSplitTableReportParams(iterable $rows, float $bancaleCost): array
    {
        // 1. Calcolo totali generali tramite il DTO LiquidationResult
        $liquidations = collect($rows)->pluck('liquidation');
        $totals = LiquidationResult::aggregateTotals($liquidations);

        // 2. Trasformazione righe per la vista
        $matrixData = collect($rows)->map(function($l) {
            $liq = $l->liquidation;

            // Gestione hydration Livewire (da stdClass a DTO)
            if ($liq instanceof \stdClass) {
                $liq = LiquidationResult::fromLivewire((array) $liq);
            }

            if (!$liq) {
                $liq = new LiquidationResult();
            }

            return $liq->toPrintParams([
                'license_number' => $l->user['license_number'] ?? '—',
                'worksMap'       => $l->worksMap,
            ]);
        })->values()->toArray();

        return [
            'view' => 'pdf.split-table',
            'data' => [
                'matrix'      => $matrixData,
                'totals'      => $totals,
                'bancaleCost' => $bancaleCost,
                'generatedBy' => Auth::user()->name ?? 'Sistema',
                'generatedAt' => now(),
                'date'        => today()
            ],
            'filename'    => 'ripartizione_' . today()->format('Ymd') . '.pdf',
            'orientation' => 'landscape',
            'paper'       => 'a4',
        ];
    }

}
