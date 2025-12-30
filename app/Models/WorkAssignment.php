<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\WorkType;

/**
 * Class WorkAssignment
 *
 * @package App\Models
 *
 * Rappresenta la singola unità di lavoro assegnata a una licenza in uno slot specifico.
 * Gestisce la logica di occupazione spaziale (multi-slot) e i vincoli di business
 * legati alle tipologie di servizio (Agenzia, Contanti, Nolo).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Business Logic Enforcement: Utilizza l'hook 'saving' per impedire stati incoerenti
 * (es. flag di esclusione su tipi di lavoro non validi).
 * 2. Spatial Constraint Management: Valida che la somma degli slot occupati non
 * superi mai il limite fisico definito nella configurazione di sistema (es. 25 slot).
 * 3. Data Integrity & Mapping: Assicura tramite Mutators che il campo 'value'
 * appartenga sempre ai casi validi dell'Enum WorkType.
 * 4. Contextual Information: Fornisce accessors per risolvere rapidamente i dati
 * dell'agenzia collegata, ottimizzando il rendering delle viste tabellari.
 *
 * LOGICA DI INTEGRITÀ:
 * - Un lavoro 'A' (Agenzia) può essere 'excluded' (non conta nel bilancio) o
 * 'shared_from_first' (condivisione costi).
 * - Un lavoro multi-slot incrementa il contatore 'usedSlots' della licenza padre.
 *
 * @property int $license_table_id
 * @property string $value (A, X, N, P)
 * @property int $slots_occupied
 * @property float $amount
 */

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
     * Boot del modello: implementa le guardie di integrità del database.
     * * Questo metodo agisce come un firewall, impedendo il salvataggio di dati
     * che violerebbero le regole di business o la coerenza fisica della matrice.
     */
    protected static function booted(): void
    {
        static::saving(function ($work) {
            $value = $work->value;

            // =================================================================
            // 1. REGOLE DI VALIDAZIONE BOOLEANI (LOGICA DI BUSINESS)
            // =================================================================
            
            // I campi 'excluded' e 'shared_from_first' hanno senso solo per 
            // lavori di tipo A (Agenzia) o X (Cash escluso).
            if ($work->excluded || $work->shared_from_first) {
                if (!in_array($value, [\App\Enums\WorkType::AGENCY->value, \App\Enums\WorkType::CASH->value])) {
                    throw new \Exception(
                        "Integrità violata: il campo 'excluded'/'shared_from_first' può essere " .
                        "abilitato solo per lavori di tipo 'A' o 'X'. Tipo attuale: '{$value}'."
                    );
                }
            }

            // =================================================================
            // 2. CONTROLLO CAPACITÀ E CONFINI FISICI (SOLO NUOVI INSERIMENTI)
            // =================================================================
            if (! $work->exists) {
                $totalSlots = config('app_settings.matrix.total_slots', 25);
                $startSlot  = (int) $work->slot;
                $newSlots   = (int) ($work->slots_occupied ?? 1);

                // A. Boundary Check: verifica che il lavoro non "esca" dal tabellone (es. slot 25 con durata 2)
                $finalSlot = $startSlot + $newSlots - 1;
                if ($finalSlot > $totalSlots) {
                    throw new \Exception(
                        "Errore Spaziale: la durata del lavoro eccede il limite del tabellone. " .
                        "Slot finale calcolato: {$finalSlot}, limite massimo: {$totalSlots}."
                    );
                }

                // B. Capacity Check: verifica che la somma degli slot usati non superi 25
                $usedSlots = self::where('license_table_id', $work->license_table_id)
                    ->sum('slots_occupied');

                if (($usedSlots + $newSlots) > $totalSlots) {
                    throw new \Exception(
                        "Errore Capacità: superata la capacità massima di {$totalSlots} slot per questa licenza. " .
                        "Già occupati: {$usedSlots}, richiesti: {$newSlots}."
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
