<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    // Nome della tabella nel database
    protected $table = 'agencies';

    // Attributi assegnabili in massa
    protected $fillable = [
        'name',  // Nome dell'agenzia
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
}
