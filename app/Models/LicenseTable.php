<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseTable extends Model
{
    use HasFactory;

    /**
     * Nome della tabella nel database
     * @var string
     */
    protected $table = 'license_table';

    /**
     * Attributi assegnabili in massa
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',          // ID dell'utente associato
        'order',            // Ordine nella lista/licenza
        'turn',             // Turno: full, morning, afternoon
        'only_cash_works',  // Flag se lavora solo con incarichi X (cash)
        'date'              // Data della licenza
    ];

    /**
     * Cast automatico degli attributi
     * @var array<string,string>
     */
    protected $casts = [
        'date' => 'date',               // Converte automaticamente in Carbon
        'only_cash_works' => 'boolean', // Converte in booleano
    ];

    // === Relazioni ===

    /**
     * Relazione con l'utente
     * Ogni licenza appartiene a un singolo utente
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con i lavori assegnati
     * Una licenza può avere molti lavori (WorkAssignment)
     */
    public function works()
    {
        return $this->hasMany(WorkAssignment::class, 'license_table_id', 'id');
    }

    // === Helper / Metodi di utilità ===

    /**
     * Controlla se il turno è Full Day
     * @return bool
     */
    public function isFullDay(): bool
    {
        return $this->turn === DayType::FULL->value;
    }

    /**
     * Controlla se il turno è Morning
     * @return bool
     */
    public function isMorning(): bool
    {
        return $this->turn === DayType::MORNING->value;
    }

    /**
     * Controlla se il turno è Afternoon
     * @return bool
     */
    public function isAfternoon(): bool
    {
        return $this->turn === DayType::AFTERNOON->value;
    }

    /**
     * Controlla se la licenza è solo per lavori cash (X)
     * @return bool
     */
    public function onlyCash(): bool
    {
        return $this->only_cash_works;
    }
}
