<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        ];
    }

    /**
     * Check if user is administrator
     */
    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    /**
     * Check if user is main proponent
     */
    public function isMainProponent(): bool
    {
        return $this->role === 'main_proponent';
    }

    /**
     * Check if user is general user
     */
    public function isGeneralUser(): bool
    {
        return $this->role === 'general_user';
    }

    /**
     * Check if user is OSAS (Office of Student Affairs and Services)
     */
    public function isOsas(): bool
    {
        return $this->role === 'osas';
    }

    /**
     * Check if user is admin or OSAS (for shared privileges)
     */
    public function isAdminOrOsas(): bool
    {
        return $this->isAdministrator() || $this->isOsas();
    }

    /**
     * Get reservations created by this user
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get emergency reports created by this user
     */
    public function emergencyReports()
    {
        return $this->hasMany(EmergencyReport::class, 'reporter_id');
    }

    /**
     * Get reservations approved by this user
     */
    public function approvedReservations()
    {
        return $this->hasMany(Reservation::class, 'approved_by');
    }
}
