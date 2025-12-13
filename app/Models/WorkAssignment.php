<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\WorkType;

class WorkAssignment extends Model
{
    use HasFactory;

    // Nome della tabella nel database
    protected $table = 'work_assignments';

    // Attributi assegnabili in massa
    protected $fillable = [
        'license_table_id',   // Riferimento alla tabella licenze
        'agency_id',          // Riferimento all'agenzia (se presente)
        'slot',               // Numero progressivo del lavoro (1,2,3,...)
        'value',              // Tipo di lavoro (A,X,N,P)
        'amount',             // Importo del lavoro (float)
        'voucher',            // Codice voucher associato
        'timestamp',          // Data e ora di creazione del lavoro
        'slots_occupied',     // Numero di slot occupati da questo lavoro
        'excluded',           // Flag se escluso (solo lavori A)
        'shared_from_first'   // Flag se condiviso dal primo (solo lavori A)
    ];

    // Cast automatici degli attributi
    protected $casts = [
        'timestamp'         => 'datetime', // Carbon
        'slots_occupied'    => 'integer',
        'slot'              => 'integer',
        'excluded'          => 'boolean',
        'shared_from_first' => 'boolean',
        'amount'            => 'float',
    ];

    // ===================================================================
    // Booted: logica di salvataggio globale
    // ===================================================================
    /**
     * Impone regole di validazione aggiuntive al momento del saving:
     * - solo lavori tipo 'A' possono avere shared_from_first o excluded true
     * - non si possono superare i 25 lavori per license_table_id
     */
protected static function booted(): void
{
    static::saving(function ($work) {
        $value = $work->value;

        // -----------------------------------------------------------------
        // REGOLE RIGIDE DI VALIDAZIONE CAMPI BOOLEANI
        // -----------------------------------------------------------------

        // 1. shared_from_first può essere TRUE **SOLO** per lavori di tipo A
        // if ($work->shared_from_first && $value !== WorkType::AGENCY->value) {
        //     throw new \Exception(
        //         "Il campo 'shared_from_first' può essere abilitato solo per lavori di tipo 'A'. " .
        //         "Valore attuale del lavoro: '{$value}'."
        //     );
        // }

        // 2. excluded e shared_from_first possono essere TRUE solo per:
        //    - lavori di tipo A (agenzia esclusa)
        //    - lavori di tipo X (cash escluso manualmente)
        if ($work->excluded || $work->shared_from_first) {
            if (!in_array($value, [WorkType::AGENCY->value, WorkType::CASH->value])) {
                throw new \Exception(
                    "Il campo 'excluded' può essere abilitato solo per lavori di tipo 'A' o 'X'. " .
                    "Valore attuale del lavoro: '{$value}'."
                );
            }
        }

        // -----------------------------------------------------------------
        // CONTROLLO CAPACITÀ SLOT (solo su creazione)
        // -----------------------------------------------------------------
        if (! $work->exists) {
            $totalSlots = config('constants.matrix.total_slots', 25);

            $usedSlots = self::where('license_table_id', $work->license_table_id)
                ->sum('slots_occupied');

            $newSlots = $work->slots_occupied ?? 1; // default 1 se non specificato

            if (($usedSlots + $newSlots) > $totalSlots) {
                throw new \Exception(
                    "Impossibile salvare il lavoro: superata la capacità massima di {$totalSlots} slot per questa licenza. " .
                    "Slot già usati: {$usedSlots}, richiesti: {$newSlots}."
                );
            }
        }
    });
}

    // ===================================================================
    // Mutators
    // ===================================================================
    /**
     * Imposta il valore solo se è un valore valido dell'enum WorkType
     * - Blocca array e oggetti
     * - Se non valido, viene impostato a null
     */
    public function setValueAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = null;
            return;
        }

        $valid = array_column(WorkType::cases(), 'value');
        $this->attributes['value'] = in_array($value, $valid, true) ? $value : null;
    }

    // ===================================================================
    // Relazioni
    // ===================================================================
    /**
     * Relazione con la license_table
     */
    public function licenseTable()
    {
        return $this->belongsTo(LicenseTable::class,'license_table_id','id');
    }

    /**
     * Relazione opzionale con l'agenzia
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    // ===================================================================
    // Accessors
    // ===================================================================
    /**
     * Nome dell'agenzia (o null se non presente)
     */
    public function getAgencyNameAttribute(): ?string
    {
        return $this->agency?->name;
    }

    /**
     * Codice dell'agenzia (o null se non presente)
     */
    public function getAgencyCodeAttribute(): ?string
    {
        return $this->agency?->code;
    }

    // ===================================================================
    // Helper methods per tipi di lavoro
    // ===================================================================
    public function isAgency(): bool
    {
        return $this->value === WorkType::AGENCY->value;
    }

    public function isCash(): bool
    {
        return $this->value === WorkType::CASH->value;
    }

    public function isNolo(): bool
    {
        return $this->value === WorkType::NOLO->value;
    }

    public function isPerdiVolta(): bool
    {
        return $this->value === WorkType::PERDI_VOLTA->value;
    }
}
