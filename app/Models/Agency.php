<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Agency
 *
 * @package App\Models
 *
 * Rappresenta un'entità esterna (Hotel, Azienda, Agenzia Viaggi) convenzionata.
 * Gestisce l'anagrafica utilizzata per raggruppare i lavori voucherizzati e
 * automatizza la pulizia della cache per garantire dati sempre aggiornati nella UI.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Persistence: Gestisce le informazioni anagrafiche con supporto SoftDeletes
 * per preservare lo storico dei lavori anche se un'agenzia viene disattivata.
 * 2. Cache Invalidation: Implementa il pattern Observer tramite i metodi boot
 * per pulire 'agencies_list' ad ogni mutazione, ottimizzando le performance della Sidebar.
 * 3. Consistent Presentation: Utilizza un Global Scope per assicurare che le liste
 * nelle tendine e nei modali siano sempre ordinate alfabeticamente.
 * 4. Identifier Mapping: Fornisce utility di lookup (findByCode) per facilitare
 * l'importazione di dati o l'inserimento rapido da interfaccia testuale.
 *
 * @property string $name
 * @property string $code
 * @property-read string $display_name
 */

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    // Nome della tabella nel database
    protected $table = 'agencies';

    // Attributi assegnabili in massa
    protected $fillable = [
        'name',  // Nome dell'agenzia
        'colour',
        'code',  // Codice identificativo dell'agenzia
    ];

    // ===================================================================
    // Boot del modello e Global Scopes
    // ===================================================================
    /**
     * Eseguito all'avvio del modello.
     * Qui si aggiunge un Global Scope per ordinare sempre le agenzie per nome.
     */
    protected static function boot()
    {
        parent::boot();

        // Ordina automaticamente per nome in modo ascendente
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });

        //cancella cache in caso di cambiamenti
        static::saved(fn() => cache()->forget('agencies_list'));
        static::deleted(fn() => cache()->forget('agencies_list'));
        static::restored(fn() => cache()->forget('agencies_list'));
    }

    // ===================================================================
    // Relazioni
    // ===================================================================

    /**
     * Un'agenzia può avere molti lavori assegnati (WorkAssignment).
     */
    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    /**
     * Un'agenzia può avere molti lavori di tipo AgencyWork.
     */
    public function agencyWorks()
    {
        return $this->hasMany(AgencyWork::class);
    }

    // ===================================================================
    // Accessors
    // ===================================================================

    /**
     * Restituisce nome e codice insieme in formato leggibile.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Trova l'agenzia dal codice
     * @param String $code
     */

    public static function findByCode(?string $code): ?self
    {
        return $code ? static::where('code', $code)->first() : null;
    }

}
