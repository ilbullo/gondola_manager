<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LicenseTable extends Model
{
    use HasFactory;

    /**
     * Nome della tabella nel database
     *
     * @var string
     */
    protected $table = 'license_table';

    /**
     * Attributi assegnabili in massa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          // ID dell'utente associato
        'order',            // Ordine nella lista/licenza
        'turn',             // Turno: full, morning, afternoon
        'only_cash_works',  // Flag se lavora solo con incarichi cash (X)
        'date'              // Data della licenza
    ];

    /**
     * Cast automatico degli attributi
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'             => 'date',     // Converte automaticamente in Carbon
        'only_cash_works'  => 'boolean',  // Converte in booleano
    ];

    // === Relazioni ===

    /**
     * Relazione con l'utente proprietario della licenza
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con i lavori assegnati alla licenza
     */
    public function works(): HasMany
    {
        return $this->hasMany(WorkAssignment::class, 'license_table_id', 'id');
    }

    // === Helper Methods per il turno ===

    public function isFullDay(): bool
    {
        return $this->turn === DayType::FULL->value;
    }

    public function isMorning(): bool
    {
        return $this->turn === DayType::MORNING->value;
    }

    public function isAfternoon(): bool
    {
        return $this->turn === DayType::AFTERNOON->value;
    }

    public function onlyCash(): bool
    {
        return $this->only_cash_works;
    }

    // === Accessor per calcoli aggregati ===

    /**
     * Capacità target: somma totale degli slot occupati dai lavori assegnati
     */
    public function getTargetCapacityAttribute(): int
    {
        return $this->works->sum('slots_occupied');
    }

    /**
     * Numero effettivo di slot occupati (coincide con target_capacity se non ci sono sovrapposizioni)
     */
    public function getSlotsOccupiedAttribute(): int
    {
        return $this->works->sum('slots_occupied');
    }

    /**
     * Wallet: importo totale incassato dalla licenza stessa (lavori con value = 'N')
     */
    public function getWalletAttribute(): int|float
    {
        return $this->works
            ->where('value', 'N')
            ->sum('amount');
    }

    // === Metodo per generare la mappa degli slot con validazione rigida ===

    /**
     * Restituisce la mappa degli slot (1-based) con i dati dei lavori assegnati.
     * Lancia eccezione in caso di sovrapposizioni o slot fuori range.
     *
     * @return array<int, array|null>
     * @throws RuntimeException
     */
    public function getWorksMapAttribute(): array
    {
        $totalSlots = config('app_settings.matrix.total_slots', 25);
        $map = array_fill(1, $totalSlots, null);

        foreach ($this->works as $work) {
            $start = $work->slot;
            $slots = $work->slots_occupied ?? 1;
            $end = $start + $slots - 1;

            // Validazione range
            if ($start < 1 || $end > $totalSlots) {
                throw new RuntimeException(
                    "WorkAssignment ID {$work->id} occupa slot fuori range ({$start}-{$end}) per licenza {$this->id}"
                );
            }

            // Controllo sovrapposizioni
            for ($i = $start; $i <= $end; $i++) {
                if ($map[$i] !== null) {
                    throw new RuntimeException(
                        "Sovrapposizione rilevata sullo slot {$i} per licenza {$this->id} " .
                        "(WorkAssignment ID {$map[$i]['id']} e {$work->id})"
                    );
                }

                $map[$i] = [
                    'id'                => $work->id,
                    'license_table_id'  => $work->license_table_id,
                    'value'             => $work->value,
                    'agency_code'       => $work->agency?->code,
                    'agency'            => $work->agency?->name,
                    'amount'            => $work->amount,
                    'voucher'           => $work->voucher,
                    'excluded'          => $work->excluded,
                    'slot'              => $work->slot,
                    'slots_occupied'    => $work->slots_occupied,
                    'shared_from_first' => $work->shared_from_first,
                    'timestamp'         => $work->timestamp?->toDateTimeString(),
                    'created_at'        => $work->created_at?->toDateTimeString(),
                    'updated_at'        => $work->updated_at?->toDateTimeString(),
                ];
            }
        }

        return $map;
    }

    // === Metodo statico per lo swap dell'ordine ===

    /**
     * Scambia l'ordine di una licenza con quella adiacente (su o giù) nella stessa data.
     *
     * @param int $id ID della licenza da spostare
     * @param string $direction 'up' per salire nell'ordine, 'down' per scendere
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException se non esiste
     */
    public static function swap(int $id, string $direction): void
    {
        DB::transaction(function () use ($id, $direction) {
            $current = self::lockForUpdate()->findOrFail($id);

            $query = self::whereDate('date', $current->date);

            $partner = $direction === 'up'
                ? $query->where('order', '<', $current->order)->orderBy('order', 'desc')->first()
                : $query->where('order', '>', $current->order)->orderBy('order', 'asc')->first();

            if ($partner) {
                $oldOrder = $current->order;
                $newOrder = $partner->order;

                // Evita violazione unique constraint su (date, order)
                $partner->update(['order' => 99999 + $partner->id]);
                $current->update(['order' => $newOrder]);
                $partner->update(['order' => $oldOrder]);
            }
        });
    }
}