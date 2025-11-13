<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyWork extends Model
{
    use HasFactory;

    protected $table = 'agency_works';

    protected $fillable = [
        'date',
        'user_id',
        'agency_id',
        'voucher',
        'amount'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Ogni lavoro appartiene a un utente.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ogni lavoro appartiene a un'agenzia.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
