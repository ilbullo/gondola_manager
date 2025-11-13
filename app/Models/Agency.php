<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Agency extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'agencies';

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Un'agenzia può avere molti lavori assegnati.
     */
    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    /**
     * Un'agenzia può avere molti agency works.
     */
    public function agencyWorks()
    {
        return $this->hasMany(AgencyWork::class);
    }


    /**
     * Accessor per visualizzare nome e codice insieme (facoltativo).
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
