<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\{UserRole, LicenseType};

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ===================================================================
    // Attributi assegnabili in massa
    // ===================================================================
    /**
     * Attributi che possono essere assegnati tramite create() o fill()
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',           // Nome dell'utente
        'username',       // Username univoco per login
        'email',          // Email di login
        'password',       // Password (verrà hashata)
        'role',           // Ruolo utente (Admin, Bancale, User)
        'type',           // Tipo di licenza (enum LicenseType)
        'license_number', // Numero licenza dell'utente
        'last_login_at'   // Ultimo login
    ];

    // ===================================================================
    // Attributi nascosti nella serializzazione
    // ===================================================================
    /**
     * Attributi nascosti quando l'utente viene serializzato in array o JSON
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ===================================================================
    // Cast automatico degli attributi
    // ===================================================================
    /**
     * Definisce come devono essere convertiti gli attributi
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Cast automatico in Carbon
            'password' => 'hashed',            // Hash automatico della password
            'role' => UserRole::class,         // Enum del ruolo
            'type' => LicenseType::class,      // Enum tipo licenza
            'last_login_at' => 'datetime'      // Ultimo login come Carbon
        ];
    }

    // ===================================================================
    // Relazioni
    // ===================================================================

    /**
     * Relazione con lavori di tipo AgencyWork
     * Un utente può avere molti lavori Agency
     */
    public function agencyWorks()
    {
        return $this->hasMany(AgencyWork::class);
    }

    /**
     * Relazione con lavori assegnati (WorkAssignment)
     * Un utente può avere molte assegnazioni
     */
    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class, 'user_id');
    }

    /**
     * Relazione con la tabella licenze (LicenseTable)
     * Un utente può avere una licenza per il giorno corrente
     */
    public function atWork()
    {
        return $this->hasOne(LicenseTable::class, 'user_id');
    }

    // ===================================================================
    // Metodi di utilità per i ruoli
    // ===================================================================

    /**
     * Verifica se l'utente è Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Verifica se l'utente è Bancale
     */
    public function isBancale(): bool
    {
        return $this->role === UserRole::BANCALE;
    }

    /**
     * Verifica se l'utente è User generico
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }
}
