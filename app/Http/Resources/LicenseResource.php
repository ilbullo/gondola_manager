<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;
use Illuminate\Support\Collection;

/**
 * LicenseResource
 *
 * Risorsa JSON per una licenza, prepara i dati per l'API.
 * Mappa tutti i lavori (works) associati alla licenza sui rispettivi slot.
 * Gestisce sovrapposizioni di slot e fornisce informazioni complete sul “wallet” della licenza.
 */
class LicenseResource extends JsonResource
{
    /**
     * Trasforma la risorsa in un array per l'API.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        try {
            $initialSlotsUsed = 0; // <-- NUOVO: Contatore per la capacità target
            // Inizializza la mappa dei 25 slot (numero totale definito in config/app_settings.php)
            $worksMap = array_fill(1, config('app_settings.matrix.total_slots'), null);

            // Cicla tutti i lavori associati alla licenza
            foreach ($this->works as $work) {
                $start = $work->slot;
                $slots = $work->slots_occupied ?? 1;
                $end   = $start + $slots - 1;
                $initialSlotsUsed += $slots; // <-- Aggiorna la capacità target

                for ($i = $start; $i <= $end; $i++) {
                    // Protezione da slot fuori range (dati corrotti)
                    if ($i < 1 || $i > config('app_settings.matrix.total_slots')) {
                        continue;
                    }

                    if (isset($worksMap[$i])) {
                        // Sovrapposizione rilevata → logga l'errore ma non interrompe la risposta
                        report(new \RuntimeException(
                            "Sovrapposizione slot: license_table_id={$this->id}, slot={$i}, work_id={$work->id}"
                        ));

                        // In locale lancia eccezione per debug
                        if (app()->environment('local')) {
                            throw new \RuntimeException("Overlap slot {$i}");
                        }
                    }

                    // Assegna i dati del lavoro nello slot corrispondente
                    $worksMap[$i] = [
                        'id'               => $work->id,
                        'license_table_id' => $work->license_table_id,
                        'value'            => $work->value,
                        'agency_code'      => $work->agency?->code,
                        'agency'           => $work->agency?->name,
                        'amount'           => $work->amount,
                        'voucher'          => $work->voucher,
                        'excluded'         => $work->excluded,
                        'slot'             => $work->slot,
                        'slots_occupied'   => $work->slots_occupied,
                        'shared_from_first'=> $work->shared_from_first,
                        'timestamp'        => $work->timestamp->toDateTimeString(),
                        'created_at'       => optional($work->created_at)->toDateTimeString(),
                        'updated_at'       => optional($work->updated_at)->toDateTimeString()
                    ];
                }
            }

            // Restituisce la struttura completa della licenza
            return [
                'id'               => $this->id,
                'license_table_id' => $this->id,
                'user'             => $this->user ? [
                    'id'            => $this->user->id,
                    'name'          => $this->user->name, 
                    'license_number'=> $this->user->license_number,
                ] : null,
                'turn'             => $this->turn,
                'only_cash_works'  => $this->only_cash_works,
                'target_capacity'  => $initialSlotsUsed,
                //'capacity'       => $this->works_count,
                'slots_occupied'   => Collection::make($worksMap)->whereNotNull()->count(),
                //'slots_occupied' => array_sum(array_column($worksMap, 'slots_occupied')),
                // “Wallet”: somma dei lavori di tipo N (incassati dalla licenza stessa)
                'wallet'           => $this->works->sum(fn ($work) => $work['value'] === 'N' ? $work['amount'] : 0),
                'worksMap'         => $worksMap,
            ];
        } catch (Throwable $e) {
            // In caso di errore grave, restituisce comunque una struttura valida con mappa vuota
            report($e);

            return [
                'id'               => $this->id,
                'license_table_id' => $this->id,
                'user'             => null,
                'worksMap'         => array_fill(1, config('app_settings.matrix.total_slots'), null),
            ];
        }
    }
}
