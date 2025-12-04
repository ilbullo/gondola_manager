<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseTable extends Model
{
    use HasFactory;

    protected $table = 'license_table';

    protected $fillable = [
        'user_id',
        'order',
        'turn',
        'only_cash_works',
        'date'
    ];

    protected $casts = [
        'date' => 'date', // Assicura che date sia castato come Carbon
        'only_cash_works' => 'boolean',
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

    public function isFullDay() {
        return $this->turn === DayType::FULL->value;
    }

    public function isMorning() {
        return $this->turn === DayType::MORNING->value;
    }

    public function isAfternoon() {
        return $this->turn === DayType::AFTERNOON->value;
    }

    public function onlyCash() {
        return $this->only_cash_works;
    }
}


