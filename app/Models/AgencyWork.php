<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AgencyWork
 *
 * @package App\Models
 *
 * Modello di persistenza per le prestazioni agenzia consolidate.
 * Rappresenta l'unità minima di fatturazione per i servizi non gestiti in contanti,
 * collegando indissolubilmente un conducente, un'agenzia e un titolo di viaggio (voucher).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Financial Auditing: Mantiene il record storico degli importi dovuti dalle agenzie
 * per ogni singolo servizio, indipendentemente dallo stato della tabella giornaliera.
 * 2. Relational Mapping: Collega l'entità User all'entità Agency, permettendo la
 * generazione di estratti conto sia per il conducente che per il partner commerciale.
 * 3. Data Casting: Garantisce l'integrità temporale tramite l'uso dell'oggetto Carbon
 * per la proprietà 'date', facilitando filtraggi cronologici precisi.
 *
 * UTILIZZO:
 * Viene popolato solitamente al termine della giornata o durante la fase di
 * redistribuzione lavori, fungendo da base per l'AgencyReportService.
 *
 * @property \Carbon\Carbon $date
 * @property int $user_id
 * @property int $agency_id
 * @property string|null $voucher
 * @property float $amount
 */

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
