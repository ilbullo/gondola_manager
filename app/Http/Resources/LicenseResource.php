<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class LicenseResource extends JsonResource
{
    public function toArray($request): array
    {
        try {
            // Inizializza la mappa dei 25 slot
            $worksMap = array_fill(1, 25, null);

            foreach ($this->works as $work) {
                $start = $work->slot;
                $end   = $start + ($work->slots_occupied ?? 1) - 1;

                for ($i = $start; $i <= $end; $i++) {
                    if ($i < 1 || $i > 25) {
                        continue; // Protezione da dati corrotti
                    }

                    if (isset($worksMap[$i])) {
                        // Sovrapposizione rilevata → logga ma non rompe tutto
                        report(new \RuntimeException(
                            "Sovrapposizione slot: license_table_id={$this->id}, slot={$i}, work_id={$work->id}"
                        ));
                        // Opzionale: in locale lancia eccezione per debug
                        if (app()->environment('local')) {
                            throw new \RuntimeException("Overlap slot {$i}");
                        }
                    }

                    $worksMap[$i] = [
                        'id'          => $work->id,
                        'license_table_id' => $work->license_table_id,
                        'value'       => $work->value,
                        'agency_code' => $work->agency?->code,
                        'agency'      => $work->agency?->name,
                        'amount'      => $work->amount,
                        'voucher'     => $work->voucher,
                        'excluded'    => $work->excluded,
                        'shared_from_first' => $work->shared_from_first,
                        'created_at'  => optional($work->created_at)->toDateTimeString(),
                        'updated_at'  => optional($work->updated_at)->toDateTimeString()
                    ];
                }
            }

            return [
                'id'               => $this->id,
                'license_table_id' => $this->id,
                'user'             => $this->user ? [
                    'id'             => $this->user->id,
                    'license_number' => $this->user->license_number,
                ] : null,
                'worksMap'         => $worksMap,
            ];
        } catch (Throwable $e) {
            // In caso di errore GRAVE, restituisci comunque una struttura valida
            report($e);

            return [
                'id'               => $this->id,
                'license_table_id' => $this->id,
                'user'             => null,
                'worksMap'         => array_fill(1, 25, null),
            ];
        }
    }
}