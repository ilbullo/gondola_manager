<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyWork extends Model
{
    use HasFactory;

    // Nome della tabella nel database
    protected $table = 'agency_works';

    // Attributi assegnabili in massa
    protected $fillable = [
        'date',       // Data del lavoro
        'user_id',    // Riferimento all'utente che ha svolto il lavoro
        'agency_id',  // Riferimento all'agenzia associata
        'voucher',    // Codice voucher se presente
        'amount'      // Importo del lavoro
    ];

    // Cast automatici degli attributi
    protected $casts = [
        'date' => 'date', // Assicura che "date" sia un Carbon
    ];

    // ===================================================================
    // Relazioni
    // ===================================================================

    /**
     * Relazione con l'utente.
     * Ogni lavoro appartiene a un utente specifico.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con l'agenzia.
     * Ogni lavoro appartiene a un'agenzia specifica.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
