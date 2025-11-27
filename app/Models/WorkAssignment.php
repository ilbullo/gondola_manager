<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAssignment extends Model
{
    use HasFactory;

    protected $table = 'work_assignments';

    protected $fillable = [
        'license_table_id',
        'agency_id',
        'slot', //la colonna corrispondente al n-esimo lavoro (es. 1,2,3,4,5...ecc)
        'value',
        'amount',
        'voucher',
        'timestamp',
        'slots_occupied',
        'excluded',
        'shared_from_first'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'slots_occupied' => 'integer',
        'slot' => 'integer',
        'excluded' => 'boolean',
        'shared_from_first' => 'boolean',
        'amount'    => 'float'
    ];


    /**
     * Relazione con l'utente.
     */
    public function licenseTable()
    {
        return $this->belongsTo(LicenseTable::class,'license_table_id','id');
    }

    /**
     * Relazione con l'agenzia (opzionale).
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Accessor per ottenere il nome effettivo dell'agenzia.
     */
    public function getAgencyNameAttribute(): ?string
    {
        return $this->agency?->name;
    }

    /**
     * Accessor per ottenere il nomcodee effettivo dell'agenzia.
     */
    public function getAgencyCodeAttribute(): ?string
    {
        return $this->agency?->code;
    }

}
