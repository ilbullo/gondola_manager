<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
{
    public function toArray($request)
    {
        $worksMap = array_fill(1,25,null);
        foreach ($this->works as $work) {
            for ($i = $work->slot; $i < $work->slot + $work->slots_occupied; $i++) {
                if (isset($worksMap[$i])) {
                    \Log::warning('Slot overlap detected', [
                        'license_id' => $this->id,
                        'slot' => $i,
                        'existing_work' => $worksMap[$i],
                        'new_work' => [
                            'id' => $work->id,
                            'value' => $work->value,
                            'slot' => $work->slot,
                            'slots_occupied' => $work->slots_occupied,
                        ],
                    ]);
                }
                $worksMap[$i] = [
                    'id' => $work->id,
                    'value' => $work->value,
                    'agency_code' => $work->agency ? $work->agency->code : null,
                    'voucher' => $work->voucher,
                    'bepi'  => "ciaone",
                    'created_at' => $work->created_at
                ];
            }
        }

        \Log::debug('WorksMap generated for license', [
            'license_id' => $this->id,
            'user_id' => $this->user_id,
            'worksMap' => $worksMap,
            'works_count' => count($this->works),
            'raw_works' => $this->works->map(fn($work) => [
                'id' => $work->id,
                'slot' => $work->slot,
                'value' => $work->value,
                'slots_occupied' => $work->slots_occupied,
            ])->toArray(),
        ]);

        return [
            'id' => $this->id,
            'license_table_id' => $this->license_table_id,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'license_number' => $this->user->license_number,
            ] : null,
            'worksMap' => $worksMap,
        ];
    }
}