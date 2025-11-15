<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseTable extends Model
{
    use HasFactory;

    protected $table = 'license_table';

    protected $fillable = [
        'user_id',
        'order',
        'date'
    ];

    protected $casts = [
        'date' => 'date', // Assicura che date sia castato come Carbon
    ];

    /**
     * Relazione con User
     * Ogni record appartiene a un specifico utente.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function works() {
        return $this->hasMany(WorkAssignment::class,'license_table_id','id');
    }
}
