<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalAcceptance extends Model
{
    // Permettiamo il mass assignment per questi campi
    protected $fillable = [
        'user_id',
        'version',
        'ip_address',
        'accepted_at',
    ];

    // Castiamo la data di accettazione come oggetto Carbon
    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Ottieni l'utente che ha effettuato l'accettazione.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}