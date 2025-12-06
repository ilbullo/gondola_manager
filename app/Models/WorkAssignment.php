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
            // Controllo shared_from_first e excluded solo per lavori A
            if (($work->shared_from_first && $work->value !== WorkType::AGENCY->value) ||
                ($work->excluded && $work->value !== WorkType::AGENCY->value))
            {
                throw new \Exception(
                    "Il campo shared_from_first o excluded può essere true solo per lavori di tipo 'A'. Valore attuale: '{$work->value}'"
                );
            }

            // Limite slot totali per nuova license_table
            /*if (! $work->exists) {
                $count = self::where('license_table_id', $work->license_table_id)->count();
                if ($count >= config('constants.matrix.total_slots')) {
                    throw new \Exception("Non puoi aggiungere più di 25 lavori per questa license_table_id.");
                }
            }*/
            if (! $work->exists) {
                $occupiedSlots = self::where('license_table_id', $work->license_table_id)
                    ->sum('slots_occupied');
                
                if ($occupiedSlots + $work->slots_occupied > config('constants.matrix.total_slots')) {
                    throw new \Exception("Capacità massima raggiunta (25 slot disponibili).");
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
