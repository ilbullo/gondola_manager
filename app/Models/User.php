<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\{UserRole, LicenseType};
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'type',
        'license_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'type' => LicenseType::class

        ];
    }

     /**
     * Un utente può avere molti lavori di tipo AgencyWork.
     */
    public function agencyWorks()
    {
        return $this->hasMany(AgencyWork::class);
    }

    /**
     * Un utente può avere molti lavori assegnati (WorkAssignment).
     */
    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class, 'user_id');
    }

    /**
     * Un utente può esser stato al lavoro molte volte (LicenseTable)
     */

    public function atWork()
    {
        return $this->hasOne(LicenseTable::class, 'user_id');
    }

    // Metodi per verificare il ruolo
    public function isAdmin()
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isBancale()
    {
        return $this->role === UserRole::BANCALE;
    }

    public function isUser()
    {
        return $this->role === UserRole::USER;
    }
}
