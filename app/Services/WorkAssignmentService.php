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
     * Esegue il salvataggio fisico dell'assegnazione con controllo conflitti e lock atomico.
     * Protegge da race conditions e garantisce l'integrità spaziale degli slot.
     */
    public function saveAssignment(int $licenseTableId, int $slot, int $slotsOccupied, array $selectedWork): void
    {
        // 1. Definizione della chiave di Lock univoca
        // La chiave è legata alla licenza e al giorno, impedendo a due processi 
        // di scrivere sulla stessa riga contemporaneamente.
        $lockKey = "save-assignment-license-{$licenseTableId}-" . today()->format('Y-m-d');
        
        // 2. Acquisizione del Lock (attesa massima 5 secondi)
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            throw new \Exception('Un altro utente sta aggiornando questa licenza. Riprova tra pochi istanti.');
        }

        try {
            // 3. Controllo sovrapposizioni slot (Integrità spaziale)
            // Verifichiamo se esistono lavori che iniziano o finiscono nel range scelto
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

            // 4. Risoluzione Agenzia
            // Se il tipo è 'A' (Agenzia), cerchiamo l'ID corrispondente dal nome ricevuto
            $agencyId = null;
            if (($selectedWork['value'] ?? '') === 'A' && !empty($selectedWork['agencyName'])) {
                $agency = Agency::where('name', $selectedWork['agencyName'])->first();
                $agencyId = $agency?->id;
            }

            // 5. Persistenza dei dati
            // Utilizziamo create() che scatenerà eventuali eventi o mutatori del modello
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

        } finally {
            // 6. Rilascio del Lock
            // Cruciale inserirlo in 'finally' per garantire lo sblocco anche in caso di eccezione
            $lock->release();
        }
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
        return (float) \App\Models\WorkAssignment::where('license_table_id', $licenseTableId)
            ->sum('amount');
    }
 
}